<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CustomerAreaController extends AbstractController
{
    #[Route('/customerarea', name: 'app_customer_area')]
    #[IsGranted("ROLE_USER")]
    public function index(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        $rentals = [];
        $profileType = 'Inconnu';

        $rentals = $user->getCompany()->getRentals();
        $profileType = 'Entreprise';
        
        // On récupère les unités de toutes les locations
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
            'units' => $units,
            'profileType' => $profileType, // Pratique pour l'afficher dans le Twig !
        ]);
    }

    #[Route('/customerarea/updateprofile', name: 'app_customer_area_update_profile')]
    #[IsGranted("ROLE_USER")]
    public function updateProfile(): Response
    {

    }

    #[Route('/customerarea/changepassword', name: 'app_customer_area_change_password')]
    #[IsGranted("ROLE_USER")]
    public function changePassword(): Response
    {

    }

    #[Route('/customerarea/seeInterventions', name: 'app_customer_area_see_interventions')]
    #[IsGranted("ROLE_USER")]
    public function seeInterventions(): Response
    {

    }
}