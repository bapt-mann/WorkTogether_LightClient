<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
final class ApiController extends AbstractController
{
    /**
     * Retourne l'état en temps réel des unités du client connecté.
     */
    #[Route('/my-units', name: 'my_units', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function getMyUnits(): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $company = $user->getCompany();

        // Si l'utilisateur n'a pas d'entreprise, on renvoie une erreur 404 en JSON
        if (!$company) {
            return $this->json(['error' => 'Aucune entreprise associée à ce compte.'], 404);
        }

        $unitsData = [];

        // boucle sur les locations et les unités pour extraire UNIQUEMENT ce qu'on veut
        foreach ($company->getRentals() as $rental) {
            foreach ($rental->getUnits() as $unit) {
                $unitsData[] = [
                    'id' => $unit->getId(),
                    'label' => $unit->getLabel(),
                    'description' => $unit->getDescription(),
                    'bay' => $unit->getBay()->getLabel(), // Ex: B014
                    'state' => $unit->getState()->getLabel(), // Ex: OK ou Incident
                    'rental_offer' => $rental->getOffer()->getLabel()
                ];
            }
        }

        // transforme notre tableau PHP en réponse JSON !
        return $this->json([
            'company' => $company->getCompanyName(),
            'total_units' => count($unitsData),
            'units' => $unitsData
        ]);
    }
}