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
                /** @var Unit $unit */
                $unit = $this->getReference('UNIT_' . $i, Unit::class);

                // On récupère l'état d'incident
                $stateIncident = $this->getReference('STATE_INCIDENT', State::class);

                // Création d'un historique "façon Journal d'Audit"
                $histo = new UnitHisto();
                $histo->setUnit($unit);
                $histo->setState($stateIncident);

                // 1. update_date : on simule un événement qui a eu lieu dans le passé
                $randomDate = new \DateTime('-' . rand(5, 100) . ' days');
                $histo->setUpdateDate($randomDate);

                // 2. unit_description : on photographie la description de l'unité
                $histo->setUnitDescription($unit->getDescription() ?? 'Aucune description');

                // 3. changes : on stocke ce qui a changé (encodé en JSON puisque c'est un VARCHAR)
                $histo->setChanges(json_encode([
                    'state' => ['OK', 'Incident']
                ]));

                // 4. rental : si l'unité est louée, on garde la trace de la location associée
                if ($unit->getRental()) {
                    $histo->setRental($unit->getRental());
                }

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
