<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use FaustDDD\Symfony1cImport\Infrastructure\Controller\Import1CController;

return function (RoutingConfigurator $routes): void {
    $routes->add('import_1c', '/1c/exchange')
        ->controller([Import1CController::class, 'exchange'])
        ->methods(['GET', 'POST']);
};