<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Rental;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PurchaseController extends AbstractController
{
    #[Route('/purchase/{id}', name: 'app_purchase')]
    #[IsGranted("ROLE_USER")]
    public function index(Offer $offer): Response
    {
        return $this->render('purchase/index.html.twig', [
            'offer' => $offer,
        ]);
    }

    #[Route('/purchase/confirm/{id}', name: 'app_purchase_confirm', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function confirm(
        Offer $offer, 
        EntityManagerInterface $em, 
        UnitRepository $unitRepository
    ): Response
    {
        $user = $this->getUser();
        $company = $user->getCompany();

        // Vérifier que l'utilisateur a bien une entreprise associée
        if (!$company) {
            $this->addFlash('error', 'Erreur critique : Aucun profil entreprise trouvé pour votre compte.');
            return $this->redirectToRoute('app_home');
        }

        // Chercher les unités libres dans le Datacenter
        // On cherche les unités où 'rental' est null, dans la limite du nombre prévu par l'offre
        $unitsNeeded = $offer->getUnitsNumber();
        $availableUnits = $unitRepository->findBy(['rental' => null], null, $unitsNeeded);

        // Si le Datacenter est plein (pas assez d'unités libres)
        if (count($availableUnits) < $unitsNeeded) {
            $this->addFlash('error', 'Désolé, nous n\'avons pas assez d\'unités disponibles dans notre datacenter pour le moment.');
            return $this->redirectToRoute('app_home'); // Ou vers la page de l'offre
        }

        // Création de la commande (Rental)
        $rental = new Rental();
        $rental->setCompany($company);
        $rental->setOffer($offer);
        $rental->setPurchaseDate(new \DateTime());

        $em->persist($rental);

        // Attribution des unités et mise en place de la description par défaut
        foreach ($availableUnits as $unit) {
            $unit->setRental($rental);
            $unit->setDescription('Unité en attente de configuration');
            $unit->setLabel($unit->getLabel() . ' (À configurer)');
        }

        // On sauvegarde le tout en base de données
        $em->flush();

        $this->addFlash('success', 'Félicitations ! Votre commande pour le pack ' . $offer->getLabel() . ' est validée. Vous pouvez maintenant configurer vos serveurs.');
        
        return $this->redirectToRoute('app_customer_area');
    }
}