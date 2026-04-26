<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use FaustDDD\Symfony1cImport\Application\Command\ImportCustomCommand;
use FaustDDD\Symfony1cImport\Application\Command\ImportOffersCommand;
use FaustDDD\Symfony1cImport\Domain\Cml\CmlFile;
use FaustDDD\Symfony1cImport\Domain\Cml\CmlMode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use ZipArchive;

final class Cml1cInteractor
{
    private string $storageDir;

    public function __construct(
        string $projectDir,
        private string $login,
        private string $password,
        private MessageBusInterface $bus,
        private Filesystem $fs
    ) {
        $this->storageDir = $projectDir . '/var/import';
        $this->fs->mkdir($this->storageDir);
    }

    public function handle(Request $request): Response
    {
        $mode = CmlMode::from($request->query->get('mode', ''));
        if ($mode->value !== 'init' && $mode->value !== 'checkauth') dd($mode);
        return match ($mode) {
            CmlMode::CheckAuth => $this->checkAuth($request),
            CmlMode::Init      => $this->init(),
            CmlMode::File      => $this->saveFile($request),
            CmlMode::Import    => $this->runImport($request),
        };
    }

    /**
     * Проверяет Basic-авторизацию
     */
    private function checkAuth(Request $request): Response
    {
        // 1С шлёт либо Basic, либо параметры ?login=1c&password=1c
        $user = $request->getUser()        ?? $request->query->get('login');
        $pass = $request->getPassword()    ?? $request->query->get('password');

        if ($user !== $this->login || $pass !== $this->password) {
            return new Response('failure\nWrong credentials', 401, [
                'Content-Type' => 'text/plain',
                'WWW-Authenticate' => 'Basic realm="1C Exchange"'
            ]);
        }

        // стандартный ответ 1С
        $cookie = 'PHPSESSID=' . session_id() . '; path=/; HttpOnly';
        $body   = "success\n" . session_name() . "\n" . $cookie . "\n";
        return new Response($body, 200, ['Content-Type' => 'text/plain']);
    }

    private function init(): Response
    {
        // можно отдать yes, если установлено расширение
        $zip = extension_loaded('zip') ? 'yes' : 'no';
        return new Response(
            "zip={$zip}\nfile_limit=0\n",
            200,
            ['Content-Type' => 'text/plain']
        );
    }

    private function saveFile(Request $request): Response
    {
        $filename = basename($request->query->get('filename', ''));
        if (!$filename) {
            return new Response('fail', 400, ['Content-Type' => 'text/plain']);
        }

        $fullPath = $this->storageDir . '/' . $filename;
        $this->fs->dumpFile($fullPath, $request->getContent());

        // ------  если пришёл zip  -------
        if (str_ends_with($filename, '.zip') && extension_loaded('zip')) {
            $zip = new ZipArchive();
            if ($zip->open($fullPath) === true) {
                $zip->extractTo($this->storageDir);
                $zip->close();
                $this->fs->remove($fullPath);   // архив больше не нужен
            }
        }

        return new Response('success', 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * @throws ExceptionInterface
     */
    private function runImportAll(): void
    {
        $files = glob($this->storageDir.'/*.xml');

        // Сортируем: import.xml первый, offers.xml второй, остальные по алфавиту
        usort($files, static function($a, $b) {
            $priority = [
                'import.xml' => 0,
                'offers.xml' => 1,
            ];
            $nameA = basename($a);
            $nameB = basename($b);
            $priorA = $priority[$nameA] ?? 99;
            $priorB = $priority[$nameB] ?? 99;

            if ($priorA !== $priorB) {
                return $priorA <=> $priorB;
            }
            return $nameA <=> $nameB; // одинаковый приоритет — по алфавиту
        });

        foreach ($files as $file) {
            $base = basename($file);
            if ($base === 'import.xml') {
                $this->bus->dispatch(new ImportCatalogCommand($file));
            } elseif ($base === 'offers.xml') {
                $this->bus->dispatch(new ImportOffersCommand($file));
            } else {
                $this->bus->dispatch(new ImportCustomCommand($file));
            }
        }
    }

    private function runImport(Request $request): Response
    {
        $uploadedName = basename($request->query->get('filename', ''));

        if (str_ends_with($uploadedName, '.zip')) {
            $this->runImportAll();
            return new Response('success', 200, ['Content-Type' => 'text/plain']);
        }

        $fullPath = $this->storageDir . '/' . $uploadedName;
        if (!file_exists($fullPath)) {
            return new Response("fail\nfile not found", 400, ['Content-Type' => 'text/plain']);
        }

        // --- исправление: проверяем имя файла ---
        if ($uploadedName === 'import.xml') {
            $this->bus->dispatch(new ImportCatalogCommand($fullPath));
        } elseif ($uploadedName === 'offers.xml') {
            $this->bus->dispatch(new ImportOffersCommand($fullPath));
        } else {
            $this->bus->dispatch(new ImportCustomCommand($fullPath));
        }

        return new Response('success', 200, ['Content-Type' => 'text/plain']);
    }
}