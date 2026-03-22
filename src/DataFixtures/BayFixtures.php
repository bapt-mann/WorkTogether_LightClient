<?php
namespace App\DataFixtures;

use App\Entity\Bay;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\State;
use App\DataFixtures\StateFixtures;

class BayFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $bay = new Bay();
            $bay->setLabel('B' . str_pad($i, 3, '0', STR_PAD_LEFT));
            $bay->setSize(42);
            $bay->setState($this->getReference(StateFixtures::STATE_OK, State::class));
            $manager->persist($bay);
            $this->addReference('BAY_' . $i, $bay);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [StateFixtures::class];
    }
}