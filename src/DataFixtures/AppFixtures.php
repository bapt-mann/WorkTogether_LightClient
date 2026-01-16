<?php

namespace App\DataFixtures;

use App\Entity\Offer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // --- CRÉATION DES OFFRES COMMERCIALES ---

        // 1. Offre BASE
        $offer1 = new Offer();
        $offer1->setLabel('Base');
        $offer1->setPrice(100.00);
        $offer1->setUnitsNumber(1);
        $offer1->setReduction(0); // Pas de réduction
        $offer1->setDurationInDays(30); // Mensuel par défaut
        $manager->persist($offer1);

        // 2. Offre START-UP
        $offer2 = new Offer();
        $offer2->setLabel('Start-up');
        $offer2->setPrice(900.00); // 100 * 10 - 10%
        $offer2->setUnitsNumber(10);
        $offer2->setReduction(10); // 10%
        $offer2->setDurationInDays(30);
        $manager->persist($offer2);

        // 3. Offre PME
        $offer3 = new Offer();
        $offer3->setLabel('PME');
        $offer3->setPrice(1680.00); // 100 * 21 - 20%
        $offer3->setUnitsNumber(21); // Demi-baie
        $offer3->setReduction(20); // 20%
        $offer3->setDurationInDays(30);
        $manager->persist($offer3);

        // 4. Offre ENTREPRISE
        $offer4 = new Offer();
        $offer4->setLabel('Entreprise');
        $offer4->setPrice(2940.00); // 100 * 42 - 30%
        $offer4->setUnitsNumber(42); // Baie complète
        $offer4->setReduction(30); // 30%
        $offer4->setDurationInDays(30);
        $manager->persist($offer4);

        // Envoi en base de données
        $manager->flush();
    }
}
