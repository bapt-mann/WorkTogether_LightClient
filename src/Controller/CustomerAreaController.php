<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Rental;
use Doctrine\ORM\EntityManagerInterface;

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

    #[Route('/customerarea/rental/{id}/toggle-renew', name: 'toggle_auto_renew', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function toggleAutoRenew(Rental $rental, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // 1. SÉCURITÉ : On vérifie que la location appartient bien à l'entreprise de l'utilisateur
        if ($rental->getCompany() !== $user->getCompany()) {
            $this->addFlash('error', 'Action non autorisée. Cette location ne vous appartient pas.');
            return $this->redirectToRoute('app_customer_area');
        }

        // 2. LOGIQUE : On inverse le booléen (si True -> False, si False -> True)
        $rental->setIsAutoRenew(!$rental->isAutoRenew());

        // 3. PERSISTANCE : On sauvegarde en base de données
        $em->flush();

        // 4. RETOUR VISUEL : On informe l'utilisateur
        $statusMessage = $rental->isAutoRenew() ? 'réactivée' : 'annulée';
        $this->addFlash('success', "La tacite reconduction a bien été $statusMessage pour l'offre " . $rental->getOffer()->getLabel() . ".");

        return $this->redirectToRoute('app_customer_area');
    }
}
