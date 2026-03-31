<?php

namespace App\Controller;

use App\Repository\RentalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Rental;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UpdateProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Psr\Log\LoggerInterface; // Importation du Logger

/**
 * Gérer l'espace client de l'utilisateur connecté.
 * Récupérer les informations du profil du compte, les locations en cours et les unités associées.
 */
final class CustomerAreaController extends AbstractController
{
    #[Route('/customerarea', name: 'app_customer_area')]
    #[IsGranted("ROLE_USER")]
    public function index(RentalRepository $rentalRepository, LoggerInterface $logger): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // Tracer l'accès au tableau de bord de l'espace client
        $logger->info('Consulter le tableau de bord de l\'espace client.', [
            'user_id' => $user->getId(),
            'company_id' => $company ? $company->getId() : null
        ]);

        $rentals = $rentalRepository->findActiveRentalsWithDetailsByCompany($company);

        $units = [];
        foreach ($rentals as $rental) {
            foreach ($rental->getUnits() as $unit) {
                $units[] = $unit;
            }
        }

        // Informer si aucune ressource n'est active
        if (empty($rentals) && empty($units)) {
            $logger->info('Afficher un espace client vide (aucune location ni unité).', [
                'user_id' => $user->getId()
            ]);
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
        LoggerInterface $logger
    ): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        $logger->info('Initier la mise à jour du profil utilisateur.', [
            'user_id' => $user->getId()
        ]);

        // Vérifier la présence obligatoire d'un profil entreprise
        if (!$company) {
            $logger->error('Refuser la mise à jour : aucun profil entreprise associé.', [
                'user_id' => $user->getId()
            ]);
            $this->addFlash('error', 'Erreur critique : Aucun profil entreprise trouvé pour votre compte.');
            return $this->redirectToRoute('app_customer_area');
        }

        $form = $this->createForm(UpdateProfileType::class, $user, [
            'companyName' => $company->getCompanyName(),
            'siret' => $company->getSiret(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Valider le nom de l'entreprise
            $companyName = $form->get('companyName')->getData();
            if ($companyName === null || trim($companyName) === '') {
                $logger->warning('Échouer lors de la validation : nom d\'entreprise vide.', [
                    'user_id' => $user->getId()
                ]);
                $form->get('companyName')->addError(new FormError(
                    'Le nom de l\'entreprise est obligatoire.'
                ));
                $entityManager->refresh($user);
                return $this->render('customer_area/update_profile.html.twig', [
                    'updateProfileForm' => $form->createView(),
                ]);
            }

            // Nettoyer et valider le format du SIRET
            $rawSiret = trim($form->get('siret')->getData());
            $siret = str_replace(' ', '', (string) $rawSiret);

            if ($siret === '' || !preg_match('/^\d{14}$/', $siret)) {
                $logger->warning('Échouer lors de la validation : format du SIRET invalide.', [
                    'user_id' => $user->getId(),
                    'siret_input' => $rawSiret
                ]);
                $form->get('siret')->addError(new FormError(
                    'Le SIRET de l\'entreprise est obligatoire et doit contenir 14 chiffres.'
                ));
                $entityManager->refresh($user);
                return $this->render('customer_area/update_profile.html.twig', [
                    'updateProfileForm' => $form->createView(),
                ]);
            }

            // Appliquer les modifications à l'entreprise
            $company->setCompanyName($companyName);
            $company->setSiret($siret);

            // Persister les données en base
            $entityManager->flush();

            $logger->info('Mettre à jour le profil avec succès.', [
                'user_id' => $user->getId(),
                'company_id' => $company->getId()
            ]);

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_customer_area');
        }

        // Afficher le formulaire initial ou réafficher en cas d'erreurs
        return $this->render('customer_area/update_profile.html.twig', [
            'updateProfileForm' => $form->createView(),
        ]);
    }

    #[Route('/customerarea/changepassword', name: 'change_password')]
    #[IsGranted("ROLE_USER")]
    public function changePassword(): Response
    {
        // En attente d'implémentation
    }

    #[Route('/customerarea/seeInterventions', name: 'see_interventions')]
    #[IsGranted("ROLE_USER")]
    public function seeInterventions(): Response
    {
        // En attente d'implémentation
    }

    #[Route('/customerarea/rental/{id}/toggle-renew', name: 'toggle_auto_renew', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function toggleAutoRenew(Rental $rental, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $logger->info('Initier la bascule du renouvellement.', [
            'user_id' => $user->getId(),
            'rental_id' => $rental->getId()
        ]);

        // Contrôler l'appartenance de la location et son statut d'activité
        if ($rental->getCompany() !== $user->getCompany() || $rental->isActive() === false) {
            $logger->warning('Refuser l\'action : location non autorisée ou inactive.', [
                'user_id' => $user->getId(),
                'rental_id' => $rental->getId()
            ]);
            $this->addFlash('error', 'Action non autorisée. Cette location ne vous appartient pas.');
            return $this->redirectToRoute('app_customer_area');
        }

        // Inverser le statut de reconduction automatique
        $rental->setIsAutoRenew(!$rental->isAutoRenew());

        // Sauvegarder la modification
        $em->flush();

        $statusMessage = $rental->isAutoRenew() ? 'réactivée' : 'annulée';

        $logger->info('Modifier le renouvellement reconduction avec succès.', [
            'rental_id' => $rental->getId(),
            'new_status' => $statusMessage
        ]);

        // Notifier l'utilisateur du changement d'état
        $this->addFlash('success', "La tacite reconduction a bien été $statusMessage pour l'offre " . $rental->getOffer()->getLabel() . ".");

        return $this->redirectToRoute('app_customer_area');
    }
}
