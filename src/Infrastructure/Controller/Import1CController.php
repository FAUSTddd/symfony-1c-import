<?php

namespace FaustDDD\Symfony1cImport\Infrastructure\Controller;

use FaustDDD\Symfony1cImport\Application\Service\Cml1cInteractor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class Import1CController extends AbstractController
{
    public function __construct(private Cml1cInteractor $interactor)
    {
    }

    #[Route('/1c/exchange', name: 'import_1c', methods: ['GET', 'POST'])]
    public function exchange(Request $request): Response
    {
        return $this->interactor->handle($request);
    }
}