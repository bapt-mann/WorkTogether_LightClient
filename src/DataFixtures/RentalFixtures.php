<?php
namespace App\DataFixtures;

use App\Entity\Rental;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Company;
use App\Entity\Offer;

class RentalFixtures extends Fixture implements DependentFixtureInterface
{
    public const TOTAL_RENTALS = 15;

    public function load(ObjectManager $manager): void
    {
        $offerTypes = ['Base', 'Start-up', 'PME', 'Entreprise'];

        for ($i = 0; $i < self::TOTAL_RENTALS; $i++) {
            $rental = new Rental();
            // On attribue aléatoirement à une des 10 entreprises
            $rental->setCompany($this->getReference('COMPANY_' . rand(0, 9), Company::class));

            // On attribue une offre au hasard
            $randomOffer = $offerTypes[array_rand($offerTypes)];
            $rental->setOffer($this->getReference('OFFER_' . $randomOffer, Offer::class));

            // Date aléatoire dans les 60 derniers jours
            $rental->setPurchaseDate(new \DateTime('-' . rand(1, 60) . ' days'));
            $rental->setIsAutoRenew(true);
            $rental->setEndDate((clone $rental->getPurchaseDate())->modify('+30 days'));
            $rental->setIsActive(true);

            $manager->persist($rental);
            $this->addReference('RENTAL_' . $i, $rental);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CompanyFixtures::class, OfferFixtures::class];
    }
}
