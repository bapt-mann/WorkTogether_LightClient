<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller permettant de gérer l'espace client de l'utilisateur connecté
 * Récuprère les informations du profil du compte, les locations en cours, les unités associées, 
 */
final class CustomerAreaController extends AbstractController
{
    #[Route('/customerarea', name: 'app_customer_area')]
    #[IsGranted("ROLE_USER")]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        $rentals = [];

        $rentals = $user->getCompany()->getRentals();
        
        // Récupère les unités de toutes les locations
        $units = [];
        foreach ($rentals as $rental) {
            foreach ($rental->getUnits() as $unit) {
                $units[] = $unit;
            }
        }

        if (empty($rentals) && empty($units)) {
            $this->addFlash('info', 'Vous n\'avez aucune location ou unité en cours.');
        }

        return $this->render('customer_area/index.html.twig', [
            'rentals' => $rentals,
            'units' => $units
        ]);
    }

    #[Route('/customerarea/updateprofile', name: 'update_profile')]
    #[IsGranted("ROLE_USER")]
    public function updateProfile(): Response
    {

    }

    #[Route('/customerarea/changepassword', name: 'change_password')]
    #[IsGranted("ROLE_USER")]
    public function changePassword(): Response
    {

    }

    #[Route('/customerarea/seeInterventions', name: 'see_interventions')]
    #[IsGranted("ROLE_USER")]
    public function seeInterventions(): Response
    {

    }
}