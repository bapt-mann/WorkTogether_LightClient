<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
final class ApiController extends AbstractController
{
    /**
     * Retourner l'état en temps réel des unités du client connecté.
     */
    #[Route('/my-units', name: 'my_units', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function getMyUnits(LoggerInterface $logger): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // Tracer l'appel à l'API pour l'utilisateur connecté
        $logger->info('Initier la récupération des unités via l\'API pour un utilisateur.', [
            'user_id' => $user->getId(),
            'user_email' => $user->getEmail()
        ]);

        // Vérifier l'existence d'une entreprise associée au compte utilisateur
        if (!$company) {

            // Enregistrer un avertissement en cas d'accès refusé
            $logger->warning('Refuser l\'accès API : aucune entreprise associée à ce compte.', [
                'user_id' => $user->getId()
            ]);

            return $this->json(['error' => 'Aucune entreprise associée à ce compte.'], 404);
        }

        $unitsData = [];

        // Parcourir les locations et extraire les données requises pour chaque unité
        foreach ($company->getRentals() as $rental) {
            foreach ($rental->getUnits() as $unit) {
                $unitsData[] = [
                    'id' => $unit->getId(),
                    'label' => $unit->getLabel(),
                    'description' => $unit->getDescription(),
                    'bay' => $unit->getBay()->getLabel(),
                    'state' => $unit->getState()->getLabel(),
                    'rental_offer' => $rental->getOffer()->getLabel()
                ];
            }
        }

        // Confirmer le traitement réussi des données avant l'envoi
        $logger->info('Retourner avec succès les données des unités au format JSON.', [
            'company_name' => $company->getCompanyName(),
            'units_count' => count($unitsData)
        ]);

        // Formater et retourner les données structurées au format JSON
        return $this->json([
            'company' => $company->getCompanyName(),
            'total_units' => count($unitsData),
            'units' => $unitsData
        ]);
    }
}
