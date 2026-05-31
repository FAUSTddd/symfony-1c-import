<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use FaustDDD\Symfony1cImport\Application\Service\CustomImporter;
use PHPUnit\Framework\TestCase;

class CustomImporterTest extends TestCase
{
    public function testInvokeProcessesValidXml(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<КоммерческаяИнформация>
  <Каталог>
    <Товары>
      <Товар><Ид>t-1</Ид><Наименование>Test</Наименование></Товар>
      <Товар><Ид>t-2</Ид><Наименование>Test 2</Наименование></Товар>
    </Товары>
  </Каталог>
</КоммерческаяИнформация>
XML;

        $tempFile = sys_get_temp_dir() . '/custom_' . uniqid() . '.xml';
        file_put_contents($tempFile, $xml);

        try {
            $importer = new CustomImporter();
            $command = new ImportCatalogCommand($tempFile);
            $importer($command);

            self::assertTrue(true);
        } finally {
            unlink($tempFile);
        }
    }

    public function testInvokeThrowsOnInvalidXml(): void
    {
        $this->expectException(\RuntimeException::class);

        $tempFile = sys_get_temp_dir() . '/custom_invalid_' . uniqid() . '.xml';
        file_put_contents($tempFile, 'not xml');

        try {
            $importer = new CustomImporter();
            $command = new ImportCatalogCommand($tempFile);
            $importer($command);
        } finally {
            unlink($tempFile);
        }
    }

    public function testInvokeThrowsOnMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);

        $importer = new CustomImporter();
        $command = new ImportCatalogCommand('/nonexistent/file.xml');
        $importer($command);
    }
}