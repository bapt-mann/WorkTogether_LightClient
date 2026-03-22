<?php
namespace App\DataFixtures;

use App\Entity\Unit;
use App\Entity\Bay;
use App\Entity\Rental;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\StateFixtures;
use App\Entity\State;

class UnitFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $currentRentalIndex = 0;
        $unitsAssignedToCurrentRental = 0;
        $incidentCounter = 0;

        // Boucle sur le datacenter physique (30 baies x 42 Unités)
        for ($i = 1; $i <= 30; $i++) {
            $bay = $this->getReference('BAY_' . $i, Bay::class);

            for ($j = 1; $j <= 42; $j++) {
                $unit = new Unit();
                $unit->setLabel('U' . str_pad($j, 2, '0', STR_PAD_LEFT));
                $unit->setSize('1.00');
                $unit->setBay($bay);
                $unit->setState($this->getReference(StateFixtures::STATE_OK, State::class));
                $unit->setDescription('Unité disponible');

                // --- ALGORITHME D'ALLOCATION CONTIGUË ---
                if ($currentRentalIndex < RentalFixtures::TOTAL_RENTALS) {
                    $rental = $this->getReference('RENTAL_' . $currentRentalIndex, Rental::class);
                    $unitsNeeded = $rental->getOffer()->getUnitsNumber();

                    // On assigne l'unité à la location en cours
                    $unit->setRental($rental);
                    $unit->setDescription('Serveur client (À configurer)');

                    // Simulation de quelques pannes aléatoires sur les serveurs loués (environ 5%)
                    if (rand(1, 100) <= 5) {
                        $unit->setState($this->getReference(StateFixtures::STATE_INCIDENT, State::class));
                        $this->addReference('INCIDENT_UNIT_' . $incidentCounter, $unit);
                        $incidentCounter++;
                    }

                    $unitsAssignedToCurrentRental++;

                    // Si on a assigné toutes les unités nécessaires pour cette location, on passe à la suivante
                    if ($unitsAssignedToCurrentRental >= $unitsNeeded) {
                        $currentRentalIndex++;
                        $unitsAssignedToCurrentRental = 0;
                    }
                }

                $manager->persist($unit);
            }
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [BayFixtures::class, StateFixtures::class, RentalFixtures::class];
    }
}