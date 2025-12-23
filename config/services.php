<?php
// config/services.php  (внутри вашего бандла)
declare(strict_types=1);

use FaustDDD\Symfony1cImport\Infrastructure\Controller\Import1CController;
use FaustDDD\Symfony1cImport\Application\Service\Cml1cInteractor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container): void {
    // служебные параметры
    $container->parameters()
        ->set('import_1c_login', '%env(IMPORT_1C_LOGIN)%')
        ->set('import_1c_password', '%env(IMPORT_1C_PASSWORD)%');

    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // авто-регистрация всего пакета
    $services->load('FaustDDD\\Symfony1cImport\\', __DIR__.'/../src/')
        ->exclude([
            __DIR__.'/../src/Domain/',
            __DIR__.'/../src/Application/Command/',
        ]);

    // явная регистрация контроллера (чтобы AbstractController не ругался)
    $services->set(Import1CController::class)
        ->public()
        ->tag('controller.service_arguments')
        ->arg('$interactor', service(Cml1cInteractor::class));

    // ручная настройка интерактора
    $services->set(Cml1cInteractor::class)
        ->arg('$projectDir', '%kernel.project_dir%')
        ->arg('$login', '%import_1c_login%')
        ->arg('$password', '%import_1c_password%');
};