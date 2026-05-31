<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit;

use FaustDDD\Symfony1cImport\DependencyInjection\Symfony1cImportExtension;
use FaustDDD\Symfony1cImport\Symfony1cImportBundle;
use PHPUnit\Framework\TestCase;

class Symfony1cImportBundleTest extends TestCase
{
    public function testGetContainerExtensionReturnsExtension(): void
    {
        $bundle = new Symfony1cImportBundle();
        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(Symfony1cImportExtension::class, $extension);
    }

    public function testGetContainerExtensionReturnsSameInstance(): void
    {
        $bundle = new Symfony1cImportBundle();
        $extension1 = $bundle->getContainerExtension();
        $extension2 = $bundle->getContainerExtension();

        self::assertSame($extension1, $extension2);
    }
}