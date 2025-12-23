<?php
// src/DependencyInjection/Symfony1cImportExtension.php
namespace FaustDDD\Symfony1cImport\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Symfony1cImportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../config'));
        $loader->load('services.yaml');

        $container->setParameter('faustddd_1c_import.endpoint', $config['endpoint']);
    }

    /**
     * Регистрируем маршрут ДО загрузки основных маршрутов
     */
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $this->processConfiguration(new Configuration(), $container->getExtensionConfig($this->getAlias()));
        $endpoint = $configs['endpoint'] ?? '/1c/exchange';

        // динамически добавляем маршрут
        $container->prependExtensionConfig('framework', [
            'router' => [
                'resource' => function (RoutingConfigurator $routes) use ($endpoint) {
                    $routes->add('import_1c', $endpoint)
                        ->controller('FaustDDD\Symfony1cImport\Infrastructure\Controller\Import1CController::exchange')
                        ->methods(['GET', 'POST']);
                },
            ],
        ]);
    }

    public function getAlias(): string
    {
        return 'faustddd_1c_import';
    }
}