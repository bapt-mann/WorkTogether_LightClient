<?php

namespace App\DataFixtures;

use App\Entity\State;
use App\Entity\Unit;
use App\Entity\UnitHisto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UnitHistoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // On génère de l'historique pour les 50 premières unités pour ne pas surcharger la BDD
        for ($i = 1; $i <= 50; $i++) {
            // On vérifie que la référence existe bien (sécurité)
            if ($this->hasReference('UNIT_' . $i, Unit::class)) {
                $unit = $this->getReference('UNIT_' . $i, Unit::class);
                
                // On récupère les états (assure-toi que les noms correspondent à ce que tu as dans StateFixtures)
                $stateOk = $this->getReference('STATE_OK', State::class);
                $stateIncident = $this->getReference('STATE_INCIDENT', State::class);

                // Création d'un historique : L'unité a été en incident dans le passé
                $histo = new UnitHisto();
                $histo->setUnit($unit);
                $histo->setState($stateIncident);
                $histo->setLabel($unit->getLabel());
                $histo->setSize($unit->getSize());

                // Date de début : il y a entre 60 et 100 jours
                $startDate = new \DateTime('-' . rand(60, 100) . ' days');
                $histo->setStartDate($startDate);
                
                // Date de fin : il y a entre 10 et 50 jours (donc l'incident est résolu)
                $endDate = (clone $startDate)->modify('+' . rand(5, 20) . ' days');
                $histo->setEndDate($endDate);

                $manager->persist($histo);
            }
        }

        $manager->flush();
    }

    // Le graphe de dépendances
    public function getDependencies(): array
    {
        return [
            UnitFixtures::class,
            StateFixtures::class,
        ];
    }
}