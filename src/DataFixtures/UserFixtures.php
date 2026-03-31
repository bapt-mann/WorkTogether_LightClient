<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Company;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail("client$i@entreprise$i.fr");
            $user->setRoles(['ROLE_USER']);
            $user->setFirstName('Contact');
            $user->setLastName('Numéro ' . $i);
            $user->setIsVerified(true);
            $user->setPassword($this->hasher->hashPassword($user, 'client123'));
            $user->setFailedLoginAttempts(0);

            // On récupère l'entreprise générée dans CompanyFixtures
            $user->setCompany($this->getReference('COMPANY_' . $i, Company::class));

            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CompanyFixtures::class];
    }
}
