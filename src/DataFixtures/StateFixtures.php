<?php
namespace App\DataFixtures;

use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\StateFixtures;

class StateFixtures extends Fixture
{
    public const STATE_OK = 'STATE_OK';
    public const STATE_INCIDENT = 'STATE_INCIDENT';
    public const STATE_MAINTENANCE = 'STATE_MAINTENANCE';

    public function load(ObjectManager $manager): void
    {
        $states = ['OK' => self::STATE_OK, 'Incident' => self::STATE_INCIDENT, 'Maintenance' => self::STATE_MAINTENANCE];

        foreach ($states as $label => $reference) {
            $state = new State();
            $state->setLabel($label);
            $manager->persist($state);
            
            // On stocke l'état en mémoire pour les autres fichiers
            $this->addReference($reference, $state);
        }
        $manager->flush();
    }
}