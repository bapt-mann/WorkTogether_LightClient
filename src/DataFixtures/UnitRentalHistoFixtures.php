<?php

namespace App\DataFixtures;

use App\Entity\Rental;
use App\Entity\Unit;
use App\Entity\UnitRentalHisto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UnitRentalHistoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // On génère un historique de location pour quelques unités
        for ($i = 51; $i <= 100; $i++) {
            
            // On utilise un ID de location aléatoire (ex: entre 1 et 5 selon ce que génère ton RentalFixtures)
            $randomRentalId = rand(1, 5); 

            if ($this->hasReference('UNIT_' . $i, Unit::class) && $this->hasReference('RENTAL_' . $randomRentalId, Rental::class)) {
                
                $unit = $this->getReference('UNIT_' . $i, Unit::class);
                $rental = $this->getReference('RENTAL_' . $randomRentalId, Rental::class);

                $histo = new UnitRentalHisto();
                $histo->setUnit($unit);
                $histo->setRental($rental); 

                // L'unité a été louée il y a longtemps
                $startDate = new \DateTime('-' . rand(150, 300) . ' days');
                $histo->setStartDate($startDate);
                
                // La location s'est terminée il y a quelques jours
                $endDate = clone $startDate;
                $endDate->modify('+' . rand(30, 90) . ' days');
                $histo->setEndDate($endDate);

                $manager->persist($histo);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UnitFixtures::class,
            RentalFixtures::class,
        ];
    }
}