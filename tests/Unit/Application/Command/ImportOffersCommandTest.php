<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Application\Command;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use FaustDDD\Symfony1cImport\Application\Command\ImportOffersCommand;
use PHPUnit\Framework\TestCase;

class ImportOffersCommandTest extends TestCase
{
    public function testConstructorStoresFilePath(): void
    {
        $command = new ImportCatalogCommand('/tmp/offers.xml');

        self::assertSame('/tmp/offers.xml', $command->filePath);
    }

    public function testSupportsEmptyPath(): void
    {
        $command = new ImportOffersCommand('');

        self::assertSame('', $command->filePath);
    }
}