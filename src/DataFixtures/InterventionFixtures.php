<?php
namespace App\DataFixtures;

use App\Entity\Intervention;
use App\Entity\Technician;
use App\Entity\Unit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InterventionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $i = 0;
        
        // On cherche toutes les unités qui ont été mises en "Incident" par le fichier UnitFixtures
        while ($this->hasReference('INCIDENT_UNIT_' . $i, Unit::class)) {
            $unit = $this->getReference('INCIDENT_UNIT_' . $i, Unit::class);
            
            $intervention = new Intervention();
            $intervention->setUnit($unit);
            $intervention->setBay($unit->getBay());
            
            // Attribue un technicien au hasard parmi les 5 créés
            $intervention->setTechnician($this->getReference('TECH_' . rand(0, 4), Technician::class));
            
            $intervention->setStartDate(new \DateTime('-' . rand(1, 5) . ' days'));
            $intervention->setEndDate(new \DateTime('+1 days'));
            $intervention->setDescription('Investigation et remplacement matériel en cours suite à une alerte critique.');
            
            $manager->persist($intervention);
            $i++;
        }
        
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [TechnicianFixtures::class, UnitFixtures::class];
    }
}