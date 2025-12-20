<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container): void {
    // служебные параметры
    $container->parameters()
        ->set('import_1c_login', '%env(IMPORT_1C_LOGIN)%')
        ->set('import_1c_password', '%env(IMPORT_1C_PASSWORD)%');

    // автоматическая регистрация всего пакета
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('FaustDDD\\Symfony1cImport\\', __DIR__.'/../src/')
        ->exclude([
            __DIR__.'/../src/Domain/',
            __DIR__.'/../src/Application/Command/',
        ]);

    // ручное переопределение (если нужно)
    $services->set(\FaustDDD\Symfony1cImport\Application\Service\Cml1cInteractor::class)
        ->arg('$projectDir', '%kernel.project_dir%')
        ->arg('$login', '%import_1c_login%')
        ->arg('$password', '%import_1c_password%');
};