<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Application\Command;

use FaustDDD\Symfony1cImport\Application\Command\ImportCustomCommand;
use PHPUnit\Framework\TestCase;

class ImportCustomCommandTest extends TestCase
{
    public function testConstructorStoresFilePath(): void
    {
        $command = new ImportCustomCommand('/tmp/custom.xml');

        self::assertSame('/tmp/custom.xml', $command->filePath);
    }
}