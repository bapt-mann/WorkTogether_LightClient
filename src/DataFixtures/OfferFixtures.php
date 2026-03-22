<?php
namespace App\DataFixtures;

use App\Entity\Offer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OfferFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $offersData = [
            ['label' => 'Base', 'units' => 1, 'price' => '100.00', 'reduction' => 0],
            ['label' => 'Start-up', 'units' => 10, 'price' => '900.00', 'reduction' => 10],
            ['label' => 'PME', 'units' => 21, 'price' => '1680.00', 'reduction' => 20],
            ['label' => 'Entreprise', 'units' => 42, 'price' => '2940.00', 'reduction' => 30],
        ];

        foreach ($offersData as $data) {
            $offer = new Offer();
            $offer->setLabel($data['label']);
            $offer->setUnitsNumber($data['units']);
            $offer->setPrice($data['price']);
            $offer->setReduction($data['reduction']);
            $offer->setDurationInDays(30);
            $offer->setIsActive(true);
            $manager->persist($offer);
            
            $this->addReference('OFFER_' . $data['label'], $offer);
        }
        $manager->flush();
    }
}