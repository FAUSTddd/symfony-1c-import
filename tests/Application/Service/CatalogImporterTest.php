<?php
declare(strict_types=1);

namespace App\Tests\Application\Service;

use App\Application\Service\CatalogImporter;
use App\Domain\Cml\CmlFile;
use App\Domain\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CatalogImporterTest extends TestCase
{
    public function testImportParsesXmlAndCallsRepository(): void
    {
        $xml = <<<XML
<?xml version="1.0"?>
<КоммерческаяИнформация>
  <Каталог><Товары><Товар><Ид>t-1</Ид><Наименование>Test</Наименование></Товар></Товары></Каталог>
</КоммерческаяИнформация>
XML;
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('save')
            ->with(self::callback(fn($dto) => $dto->id === 't-1'));

        $importer = new CatalogImporter($repo);
        $importer->import(CmlFile::fromString($xml));
    }
}