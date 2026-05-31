<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Unit\DependencyInjection;

use FaustDDD\Symfony1cImport\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultEndpoint(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        self::assertSame('/import/1c-exchange', $config['endpoint']);
    }

    public function testCustomEndpoint(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [
            ['endpoint' => '/custom/1c'],
        ]);

        self::assertSame('/custom/1c', $config['endpoint']);
    }

    public function testEmptyEndpointThrowsException(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [
            ['endpoint' => ''],
        ]);
    }
}