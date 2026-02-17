<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CustomerAreaController extends AbstractController
{
    #[Route('/customerarea', name: 'app_customer_area')]
    public function index(): Response
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('app_login');
        }
        else {
            return $this->render('customer_area/index.html.twig', [
                'controller_name' => 'CustomerAreaController',
            ]);
        }
    }
}
