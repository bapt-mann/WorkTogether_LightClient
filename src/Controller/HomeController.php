<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use Psr\Log\LoggerInterface; // Importation du Logger
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Gérer la page d'accueil de l'application.
 * Récupérer les offres actives en base de données et les restituer à la vue.
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(OfferRepository $offerRepository, LoggerInterface $logger): Response
    {
        // Tracer l'accès à la page d'accueil
        $logger->info('Consulter la page d\'accueil publique.');

        // Extraire les offres disponibles triées par prix croissant
        $offers = $offerRepository->findBy(['isActive' => true], ['price' => 'ASC']);

        // Rendre le template d'affichage avec les données récupérées
        return $this->render('home/index.html.twig', [
            'offers' => $offers,
        ]);
    }
}
