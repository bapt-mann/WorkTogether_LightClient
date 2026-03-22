<?php
namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CompanyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $company = new Company();
            $company->setCompanyName('Entreprise Cloud ' . $i);
            // Génération d'un faux SIRET aléatoire
            $company->setSiret('123456789012' . str_pad($i, 2, '0', STR_PAD_LEFT));
            
            $manager->persist($company);
            $this->addReference('COMPANY_' . $i, $company);
        }
        $manager->flush();
    }
}