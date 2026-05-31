<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Application\Command;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use PHPUnit\Framework\TestCase;

class ImportCatalogCommandTest extends TestCase
{
    public function testConstructorStoresFilePath(): void
    {
        $command = new ImportCatalogCommand('/tmp/import.xml');

        self::assertSame('/tmp/import.xml', $command->filePath);
    }

    public function testSupportsEmptyPath(): void
    {
        $command = new ImportCatalogCommand('');

        self::assertSame('', $command->filePath);
    }
}