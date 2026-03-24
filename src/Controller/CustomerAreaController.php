<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Rental;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UpdateProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;


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

        dump($rentals);
        dump($units);

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
    public function updateProfile(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $user = $this->getUser();
        $company = $user->getCompany();
        
        $form = $this->createForm(UpdateProfileType::class, $user, [
            'companyName' => $company ? $company->getCompanyName() : '',
            'siret' => $company ? $company->getSiret() : '',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $companyName = $form->get('companyName')->getData();
            if ($companyName === null) {
                $form->get('companyName')->addError(new FormError(
                    'Le nom de l\'entreprise est obligatoire.'
                ));
                return $this->render('register/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $siret = $form->get('siret')->getData();
            if ($siret === null || !preg_match('/^\d{14}$/', $siret)) {
                $form->get('siret')->addError(new FormError(
                    'Le SIRET de l\'entreprise est obligatoire et doit contenir 14 chiffres.'
                ));
                return $this->render('register/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            if ($company) {
                $company->setCompanyName($companyName);
                $company->setSiret($siret);
            }
            else {
                $this->addFlash('error', 'Erreur critique : Aucun profil entreprise trouvé pour votre compte.');
                dump('Erreur critique : Aucun profil entreprise trouvé pour votre compte.');
                return $this->redirectToRoute('app_customer_area');
            }

            $user->setEmail($form->get('email')->getData());
            $user->setFirstName($form->get('firstName')->getData());
            $user->setLastName($form->get('lastName')->getData());

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            dump('Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_customer_area');
        }

        return $this->render('customer_area/update_profile.html.twig', [
            'updateProfileForm' => $form->createView(),
        ]);
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

        // Vérifie que la location appartient bien à l'entreprise de l'utilisateur
        if ($rental->getCompany() !== $user->getCompany()) {
            $this->addFlash('error', 'Action non autorisée. Cette location ne vous appartient pas.');
            return $this->redirectToRoute('app_customer_area');
        }

        // Inverse le booléen 
        $rental->setIsAutoRenew(!$rental->isAutoRenew());

        // Sauvegarde en base de données
        $em->flush();

        // Informe l'utilisateur
        $statusMessage = $rental->isAutoRenew() ? 'réactivée' : 'annulée';
        $this->addFlash('success', "La tacite reconduction a bien été $statusMessage pour l'offre " . $rental->getOffer()->getLabel() . ".");

        return $this->redirectToRoute('app_customer_area');
    }
}
