<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Application\Service;

use FaustDDD\Symfony1cImport\Application\Command\ImportOffersCommand;
use FaustDDD\Symfony1cImport\Application\Service\OffersImporter;
use PHPUnit\Framework\TestCase;

class OffersImporterTest extends TestCase
{
    public function testInvokeProcessesValidXml(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<КоммерческаяИнформация>
  <ПакетПредложений>
    <Предложения>
      <Предложение><Ид>o-1</Ид><Наименование>Offer 1</Наименование></Предложение>
      <Предложение><Ид>o-2</Ид><Наименование>Offer 2</Наименование></Предложение>
    </Предложения>
  </ПакетПредложений>
</КоммерческаяИнформация>
XML;

        $tempFile = sys_get_temp_dir() . '/offers_' . uniqid() . '.xml';
        file_put_contents($tempFile, $xml);

        try {
            $importer = new OffersImporter();
            $command = new ImportOffersCommand($tempFile);
            $importer($command);

            self::assertTrue(true);
        } finally {
            unlink($tempFile);
        }
    }

    public function testInvokeThrowsOnInvalidXml(): void
    {
        $this->expectException(\RuntimeException::class);

        $tempFile = sys_get_temp_dir() . '/offers_invalid_' . uniqid() . '.xml';
        file_put_contents($tempFile, 'not xml');

        try {
            $importer = new OffersImporter();
            $command = new ImportOffersCommand($tempFile);
            $importer($command);
        } finally {
            unlink($tempFile);
        }
    }

    public function testInvokeThrowsOnMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);

        $importer = new OffersImporter();
        $command = new ImportOffersCommand('/nonexistent/file.xml');
        $importer($command);
    }
}