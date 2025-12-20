<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use FaustDDD\Symfony1cImport\Domain\Cml\CmlFile;
use FaustDDD\Symfony1cImport\Domain\Cml\CmlMode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
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
            return new Response('fail', 400);
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

        return new Response('success');
    }

    private function runImport(Request $request): Response
    {
        $uploadedName = basename($request->query->get('filename', ''));
        $dir = $this->storageDir;

        // если прислали zip-имя, но распаковали – ищем реальный XML
        if (str_ends_with($uploadedName, '.zip')) {
            $files = glob($dir . '/*.xml');
            if (!$files) {
                return new Response("fail\nno xml after unzip", 400);
            }
            $fullPath = $files[0];   // берём первый
        } else {
            $fullPath = $dir . '/' . $uploadedName;
        }

        if (!file_exists($fullPath)) {
            return new Response("fail\nfile not found", 400);
        }

        $this->bus->dispatch(new ImportCatalogCommand($fullPath));

        return new Response('success');
    }
}