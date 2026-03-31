<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Rental;
use App\Entity\Unit;
use App\Entity\UnitRentalHisto;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface; // Importation du Logger
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

/**
 * Gérer le processus d'achat des offres et la configuration des unités.
 * Afficher les détails de l'offre, valider la disponibilité dans le datacenter,
 * créer la location et attribuer les serveurs au client.
 */
final class PurchaseController extends AbstractController
{
    #[Route('/purchase/{id}', name: 'app_purchase')]
    #[IsGranted("ROLE_USER")]
    public function index(Offer $offer, LoggerInterface $logger): Response
    {
        // Tracer la consultation d'une offre spécifique
        $logger->info('Consulter la page de détail d\'une offre.', [
            'offer_id' => $offer->getId(),
            'offer_label' => $offer->getLabel()
        ]);

        return $this->render('purchase/index.html.twig', [
            'offer' => $offer,
        ]);
    }

    #[Route('/purchase/confirm/{id}', name: 'app_purchase_confirm', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function confirm(
        Offer $offer,
        EntityManagerInterface $em,
        UnitRepository $unitRepository,
        LoggerInterface $logger
    ): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        $logger->info('Initier la confirmation d\'achat pour une offre.', [
            'user_id' => $user->getId(),
            'offer_id' => $offer->getId()
        ]);

        // Vérifier la présence d'un profil entreprise associé au compte
        if (!$company) {
            $logger->error('Refuser l\'achat : aucun profil entreprise trouvé.', [
                'user_id' => $user->getId()
            ]);
            $this->addFlash('error', 'Erreur critique : Aucun profil entreprise trouvé pour votre compte.');
            return $this->redirectToRoute('app_home');
        }

        // Rechercher les unités libres dans le Datacenter en fonction de la capacité de l'offre
        $unitsNeeded = $offer->getUnitsNumber();
        $availableUnits = $unitRepository->findBy(['rental' => null], null, $unitsNeeded);

        // Valider la capacité du datacenter à répondre à la demande
        if (count($availableUnits) < $unitsNeeded) {
            $logger->critical('Échouer lors de l\'achat : capacité insuffisante dans le datacenter.', [
                'units_needed' => $unitsNeeded,
                'units_available' => count($availableUnits),
                'offer_id' => $offer->getId()
            ]);
            $this->addFlash('error', 'Désolé, nous n\'avons pas assez d\'unités disponibles dans notre datacenter pour le moment.');
            return $this->redirectToRoute('app_home');
        }

        // Instancier et configurer la nouvelle location
        $rental = new Rental();
        $rental->setCompany($company);
        $rental->setOffer($offer);
        $rental->setPurchaseDate(new \DateTime());
        $rental->setIsAutoRenew(true);
        $rental->setIsActive(true); // Sécuriser l'état actif de la commande par défaut
        $rental->setEndDate((new \DateTime())->modify('+1 month'));

        $em->persist($rental);

        // Assigner les unités disponibles à cette nouvelle location
        foreach ($availableUnits as $unit) {
            $unit->setRental($rental);
            $unit->setDescription('Unité en attente de configuration');
            $unit->setLabel($unit->getLabel() . ' (À configurer)');

            // Historiser l'attribution de l'unité dans le journal des locations
            $histo = new UnitRentalHisto();
            $histo->setUnit($unit);
            $histo->setRental($rental);
            $histo->setStartDate(new \DateTime());
            $em->persist($histo);
        }

        // Sauvegarder l'ensemble de la transaction en base de données
        $em->flush();

        $logger->info('Valider la commande et attribuer les unités avec succès.', [
            'rental_id' => $rental->getId(),
            'company_id' => $company->getId(),
            'assigned_units_count' => count($availableUnits)
        ]);

        $this->addFlash('success', 'Félicitations ! Votre commande pour le pack ' . $offer->getLabel() . ' est validée. Vous pouvez maintenant configurer vos serveurs.');

        return $this->redirectToRoute('app_customer_area');
    }

    #[Route('/unit/configure/{id}', name: 'app_unit_configure', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function configureUnit(
        Unit $unit,
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Contrôler l'appartenance de l'unité à l'entreprise de l'utilisateur
        $userCompany = $user->getCompany();
        $unitCompany = $unit->getRental() ? $unit->getRental()->getCompany() : null;

        $logger->info('Initier la configuration d\'une unité.', [
            'user_id' => $user->getId(),
            'unit_id' => $unit->getId()
        ]);

        if ($userCompany !== $unitCompany) {
            $logger->warning('Refuser la configuration : l\'unité n\'appartient pas à l\'utilisateur.', [
                'user_id' => $user->getId(),
                'unit_id' => $unit->getId()
            ]);
            $this->addFlash('error', 'Accès refusé. Cette unité ne vous appartient pas.');
            return $this->redirectToRoute('app_customer_area');
        }

        // Extraire les données de la requête entrante
        $newLabel = $request->request->get('label');
        $newDescription = $request->request->get('description');

        // Valider et appliquer la nouvelle configuration
        if ($newLabel && $newDescription) {
            $unit->setLabel($newLabel);
            $unit->setDescription($newDescription);

            $em->flush();

            $logger->info('Mettre à jour la configuration de l\'unité avec succès.', [
                'unit_id' => $unit->getId()
            ]);

            $this->addFlash('success', 'La configuration du serveur a été mise à jour avec succès !');
        } else {
            $logger->warning('Échouer lors de la configuration : données de formulaire incomplètes.', [
                'unit_id' => $unit->getId()
            ]);
            $this->addFlash('error', 'Veuillez remplir tous les champs.');
        }

        // Rediriger vers l'espace client pour visualiser les changements
        return $this->redirectToRoute('app_customer_area');
    }
}
