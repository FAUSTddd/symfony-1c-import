<?php
declare(strict_types=1);

namespace FaustDDD\Symfony1cImport\Tests\Functional\Infrastructure\Controller;

use FaustDDD\Symfony1cImport\Application\Service\Cml1cInteractor;
use FaustDDD\Symfony1cImport\Infrastructure\Controller\Import1CController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Import1CControllerTest extends TestCase
{
    public function testExchangeDelegatesToInteractor(): void
    {
        $bus = $this->createMock(\Symfony\Component\Messenger\MessageBusInterface::class);
        $fs = new \Symfony\Component\Filesystem\Filesystem();

        $interactor = new Cml1cInteractor(
            sys_get_temp_dir(),
            'admin',
            'admin',
            $bus,
            $fs
        );

        $controller = new Import1CController($interactor);
        $request = Request::create('/1c_exchange?mode=checkauth', 'GET', ['login' => 'admin', 'password' => 'admin']);

        $response = $controller->exchange($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}