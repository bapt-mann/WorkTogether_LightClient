<?php
namespace App\DataFixtures;

use App\Entity\Technician;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TechnicianFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $names = ['Alice Expert-Réseau', 'Bob Câblage', 'Charlie Serveur', 'Diane Fibre', 'Éric Système'];

        foreach ($names as $i => $name) {
            $tech = new Technician();
            $tech->setLabel($name);
            $manager->persist($tech);
            $this->addReference('TECH_' . $i, $tech);
        }
        $manager->flush();
    }
}
