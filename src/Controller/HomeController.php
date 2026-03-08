<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller de la page d'accueil 
 * Récupère les offres en base de données et les affiche dans la page d'accueil
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(OfferRepository $offerRepository): Response
    {
        // On récupère toutes les offres triées par prix croissant (optionnel mais recommandé)
        $offers = $offerRepository->findAll();

        return $this->render('home/index.html.twig', [
            'offers' => $offers,
        ]);
    }
}
