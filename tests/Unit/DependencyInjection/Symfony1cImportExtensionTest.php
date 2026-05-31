<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\DependencyInjection;

use FaustDDD\Symfony1cImport\DependencyInjection\Symfony1cImportExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Symfony1cImportExtensionTest extends TestCase
{
    public function testLoadSetsEndpointParameter(): void
    {
        $container = new ContainerBuilder();
        $extension = new Symfony1cImportExtension();

        $extension->load([['endpoint' => '/test/1c']], $container);

        self::assertTrue($container->hasParameter('faustddd_1c_import.endpoint'));
        self::assertSame('/test/1c', $container->getParameter('faustddd_1c_import.endpoint'));
    }

    public function testGetAlias(): void
    {
        $extension = new Symfony1cImportExtension();

        self::assertSame('faustddd_1c_import', $extension->getAlias());
    }
}