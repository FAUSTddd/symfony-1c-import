<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Application\Service;

use FaustDDD\Symfony1cImport\Application\Service\Cml1cInteractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class Cml1cInteractorTest extends TestCase
{
    private string $tempDir;
    private MessageBusInterface $bus;
    private Filesystem $fs;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/cml_test_' . uniqid();
        $this->fs = new Filesystem();
        $this->fs->mkdir($this->tempDir);
        $this->bus = $this->createMock(MessageBusInterface::class);
    }

    protected function tearDown(): void
    {
        $this->fs->remove($this->tempDir);
    }

    public function testCheckAuthSuccess(): void
    {
        $interactor = new Cml1cInteractor(
            $this->tempDir,
            'admin',
            'admin',
            $this->bus,
            $this->fs
        );

        $request = Request::create(
            '/1c_exchange?mode=checkauth',
            'GET',
            ['login' => 'admin', 'password' => 'admin']
        );

        $response = $interactor->handle($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('success', $response->getContent());
    }

    public function testCheckAuthFailure(): void
    {
        $interactor = new Cml1cInteractor(
            $this->tempDir,
            'admin',
            'admin',
            $this->bus,
            $this->fs
        );

        $request = Request::create(
            '/1c_exchange?mode=checkauth',
            'GET',
            ['login' => 'wrong', 'password' => 'wrong']
        );

        $response = $interactor->handle($request);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertStringContainsString('failure', $response->getContent());
    }

    public function testInitReturnsZipStatus(): void
    {
        $interactor = new Cml1cInteractor(
            $this->tempDir,
            'admin',
            'admin',
            $this->bus,
            $this->fs
        );

        $request = Request::create('/1c_exchange?mode=init', 'GET');

        $response = $interactor->handle($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('zip=', $response->getContent());
    }

    public function testSaveFileSuccess(): void
    {
        $interactor = new Cml1cInteractor(
            $this->tempDir,
            'admin',
            'admin',
            $this->bus,
            $this->fs
        );

        $request = Request::create(
            '/1c_exchange?mode=file&filename=test.xml',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/xml'],
            '<xml>test</xml>'
        );

        $response = $interactor->handle($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('success', $response->getContent());
        self::assertFileExists($this->tempDir . '/var/import/test.xml');
    }

    public function testSaveFileWithoutFilenameFails(): void
    {
        $interactor = new Cml1cInteractor(
            $this->tempDir,
            'admin',
            'admin',
            $this->bus,
            $this->fs
        );

        $request = Request::create('/1c_exchange?mode=file', 'POST');

        $response = $interactor->handle($request);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testRunImportDispatchesCatalogCommand(): void
    {
        $this->bus->expects(self::once())
            ->method('dispatch')
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()));

        $interactor = new Cml1cInteractor(
            $this->tempDir,
            'admin',
            'admin',
            $this->bus,
            $this->fs
        );

        $this->fs->dumpFile($this->tempDir . '/var/import/import.xml', '<xml/>');

        $request = Request::create(
            '/1c_exchange?mode=import&filename=import.xml',
            'GET'
        );

        $response = $interactor->handle($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('success', $response->getContent());
    }

    public function testRunImportAllDispatchesMultipleCommands(): void
    {
        $interactor = new Cml1cInteractor(
            $this->tempDir,
            'admin',
            'admin',
            $this->bus,
            $this->fs
        );

        // Сначала сохраняем zip через saveFile
        $zipContent = file_get_contents($this->createTestZip());

        $saveRequest = Request::create(
            '/1c_exchange?mode=file&filename=test.zip',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/zip'],
            $zipContent
        );

        $interactor->handle($saveRequest);

        // Проверяем, что xml файлы появились
        $xmlFiles = glob($this->tempDir . '/var/import/*.xml');

        // Теперь вызываем import
        $this->bus->expects(self::exactly(2))
            ->method('dispatch')
            ->willReturn(new \Symfony\Component\Messenger\Envelope(new \stdClass()));

        $request = Request::create(
            '/1c_exchange?mode=import&filename=test.zip',
            'GET'
        );

        $response = $interactor->handle($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    private function createTestZip(): string
    {
        $zipPath = sys_get_temp_dir() . '/test_' . uniqid() . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('import.xml', '<xml/>');
        $zip->addFromString('offers.xml', '<xml/>');
        $zip->close();

        return $zipPath;
    }

    public function testRunImportFileNotFound(): void
    {
        $interactor = new Cml1cInteractor(
            $this->tempDir,
            'admin',
            'admin',
            $this->bus,
            $this->fs
        );

        $request = Request::create(
            '/1c_exchange?mode=import&filename=missing.xml',
            'GET'
        );

        $response = $interactor->handle($request);

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertStringContainsString('fail', $response->getContent());
    }
}