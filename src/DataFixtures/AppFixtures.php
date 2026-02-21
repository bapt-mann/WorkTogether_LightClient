<?php

namespace App\DataFixtures;

use App\Entity\Bay;
use App\Entity\Company;
use App\Entity\Intervention;
use App\Entity\Offer;
use App\Entity\Rental;
use App\Entity\State;
use App\Entity\Technician;
use App\Entity\Unit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. CRÉATION DES ÉTATS
        $states = ['OK', 'Incident', 'Maintenance'];
        $stateEntities = [];
        foreach ($states as $label) {
            $state = new State();
            $state->setLabel($label);
            $manager->persist($state);
            $stateEntities[$label] = $state;
        }

        // 2. CRÉATION DES OFFRES
        $offersData = [
            ['label' => 'Base', 'units' => 1, 'price' => '100.00', 'reduction' => 0],
            ['label' => 'Start-up', 'units' => 10, 'price' => '900.00', 'reduction' => 10],
            ['label' => 'PME', 'units' => 21, 'price' => '1680.00', 'reduction' => 20],
            ['label' => 'Entreprise', 'units' => 42, 'price' => '2940.00', 'reduction' => 30],
        ];

        $offerPME = null;
        foreach ($offersData as $data) {
            $offer = new Offer();
            $offer->setLabel($data['label']);
            $offer->setUnitsNumber($data['units']);
            $offer->setPrice($data['price']);
            $offer->setReduction($data['reduction']);
            $offer->setDurationInDays(30);
            $manager->persist($offer);
            
            if ($data['label'] === 'PME') {
                $offerPME = $offer; // On met l'offre PME de côté pour Jean
            }
        }

        // 3. CRÉATION DU PROFIL CLIENT "JEAN"
        $customerJean = new Company();
        $customerJean->setCompanyName('Jean Dupont');
        $customerJean->setSiret('98765432109876');
        $manager->persist($customerJean);

        $company = new Company();
        $company->setCompanyName('Entreprise SA');
        $company->setSiret('12345678901234');
        $manager->persist($company);


        // 4. CRÉATION D'UNE LOCATION 
        $rentalJean = new Rental();
        $rentalJean->setCompany($customerJean);
        $rentalJean->setOffer($offerPME);
        $rentalJean->setPurchaseDate(new \DateTime('-15 days')); // Achetée il y a 15 jours
        $manager->persist($rentalJean);

        // 5. CRÉATION D'UN TECHNICIEN
        $technician = new Technician();
        $technician->setLabel('Alice Expert-Réseau');
        $manager->persist($technician);

        // 6. CRÉATION DES BAIES ET UNITÉS
        $incidentUnit = null;

        for ($i = 1; $i <= 30; $i++) {
            $bay = new Bay();
            $bay->setLabel('B' . str_pad($i, 3, '0', STR_PAD_LEFT));
            $bay->setSize(42);
            $bay->setState($stateEntities['OK']);
            $manager->persist($bay);

            for ($j = 1; $j <= 42; $j++) {
                $unit = new Unit();
                $unit->setLabel('U' . str_pad($j, 2, '0', STR_PAD_LEFT));
                $unit->setSize('1.00');
                $unit->setBay($bay);
                $unit->setDescription('oui');

                // SCÉNARIO : On attribue les 21 premières unités de la baie B014 à Jean
                if ($i === 14 && $j <= 21) {
                    $unit->setRental($rentalJean);

                    // On simule une panne sur l'unité U02
                    if ($j === 2) {
                        $unit->setState($stateEntities['Incident']);
                        $incidentUnit = $unit;
                    } else {
                        $unit->setState($stateEntities['OK']);
                    }
                } else {
                    $unit->setState($stateEntities['OK']);
                }
                $manager->persist($unit);
            }
        }

        // 7. CRÉATION DE L'INTERVENTION SUR L'UNITÉ EN PANNE
        if ($incidentUnit) {
            $intervention = new Intervention();
            $intervention->setUnit($incidentUnit);
            $intervention->setBay($incidentUnit->getBay());
            $intervention->setTechnician($technician);
            $intervention->setStartDate(new \DateTime('-1 days'));
            $intervention->setEndDate(new \DateTime('+2 days')); // Prévue pour demain
            $intervention->setDescription('Remplacement du commutateur réseau défaillant.');
            $manager->persist($intervention);
        }

        // 8. CRÉATION DU COMPTE UTILISATEUR DE JEAN (Pour la connexion)
        $client = new User();
        $client->setEmail('client@entreprise.fr');
        $client->setRoles(['ROLE_USER']);
        //$client->setFirstName('Jean');
        //$client->setLastName('Dupont');
        $client->setIsVerified(true);
        $client->setPassword($this->hasher->hashPassword($client, 'client123'));
        $client->setCompany($customerJean); 
        
        $manager->persist($client);

        $client = new User();
        $client->setEmail('entreprise@entreprise.fr');
        $client->setRoles(['ROLE_USER']);
        $client->setFirstName('Jane');
        $client->setIsVerified(true);
        $client->setPassword($this->hasher->hashPassword($client, 'client123'));
        $client->setCompany($company);

        $manager->persist($client);

        // Enregistrement final en BDD
        $manager->flush();
    }
}