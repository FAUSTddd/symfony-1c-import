<?php
declare(strict_types=1);

use FaustDDD\Symfony1cImport\Infrastructure\Controller\Import1CController;
use FaustDDD\Symfony1cImport\Application\Service\Cml1cInteractor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    // 1. Регистрируем контроллер ПЕРВЫМ и явно
    $services->set(Import1CController::class)
        ->public()
        ->tag('controller.service_arguments')
        ->arg('$interactor', service(Cml1cInteractor::class));

    // 2. Остальные сервисы
    $services->load('FaustDDD\\Symfony1cImport\\', __DIR__.'/../src/')
        ->exclude([
            __DIR__.'/../src/Domain/',
            __DIR__.'/../src/Application/Command/',
            __DIR__.'/../src/Infrastructure/Controller/', // <-- исключаем, чтобы не дублировать
        ]);

    $services->set(Cml1cInteractor::class)
        ->arg('$projectDir', '%kernel.project_dir%')
        ->arg('$login', '%env(IMPORT_1C_LOGIN)%')
        ->arg('$password', '%env(IMPORT_1C_PASSWORD)%');
};