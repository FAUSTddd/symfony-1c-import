<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\Application\Command;

use FaustDDD\Symfony1cImport\Application\Command\ImportCatalogCommand;
use FaustDDD\Symfony1cImport\Application\Command\ImportCustomCommand;
use FaustDDD\Symfony1cImport\Application\Command\ImportOffersCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ImportCommandsTest extends TestCase
{
    /**
     * @return array<string, array{class-string, string}>
     */
    public static function commandClassesProvider(): array
    {
        return [
            'catalog' => [ImportCatalogCommand::class, '/tmp/catalog.xml'],
            'custom'  => [ImportCustomCommand::class, '/tmp/custom.xml'],
            'offers'  => [ImportOffersCommand::class, '/tmp/offers.xml'],
        ];
    }

    #[DataProvider('commandClassesProvider')]
    public function testCommandStoresFilePath(string $className, string $filePath): void
    {
        $command = new $className($filePath);

        self::assertSame($filePath, $command->filePath);
    }
}