<?php
namespace App\DataFixtures;

use App\Entity\Technician;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TechnicianFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $tech = new Technician();
            $manager->persist($tech);
            $this->addReference('TECH_' . $i, $tech);
        }
        $manager->flush();
    }
}
