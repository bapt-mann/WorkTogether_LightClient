<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConnectionController extends AbstractController
{
    #[Route('/connection', name: 'app_connection')]
    public function index(): Response
    {
        return $this->render('connection/index.html.twig', [
            'controller_name' => 'ConnectionController',
        ]);
    }
}
