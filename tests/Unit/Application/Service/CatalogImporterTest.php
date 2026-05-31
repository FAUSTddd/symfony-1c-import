<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use FaustDDD\Symfony1cImport\Application\Service\CatalogImporter;
use PHPUnit\Framework\TestCase;

class CatalogImporterTest extends TestCase
{
    public function testInvokeDoesNotThrowOnValidXml(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<КоммерческаяИнформация>
  <Каталог>
    <Товары>
      <Товар><Ид>t-1</Ид><Наименование>Test</Наименование></Товар>
    </Товары>
  </Каталог>
</КоммерческаяИнформация>
XML;

        $tempFile = sys_get_temp_dir() . '/catalog_' . uniqid() . '.xml';
        file_put_contents($tempFile, $xml);

        try {
            $importer = new CatalogImporter();
            $command = new ImportCatalogCommand($tempFile);
            $importer($command);

            self::assertTrue(true);
        } finally {
            unlink($tempFile);
        }
    }

    /**
     * @throws \Throwable
     */
    public function testInvokeThrowsOnMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);

        $importer = new CatalogImporter();
        $command = new ImportCatalogCommand('/nonexistent/file.xml');
        $importer($command);
    }
}