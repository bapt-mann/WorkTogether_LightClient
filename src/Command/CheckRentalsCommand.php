<?php

namespace App\Command;

use App\Repository\RentalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-rentals',
    description: 'Vérifie les locations expirées pour les renouveler ou libérer les serveurs.',
)]
class CheckRentalsCommand extends Command
{
    public function __construct(
        private RentalRepository $rentalRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();

        // 1. On cherche toutes les locations dont la date de fin est dépassée (<= maintenant)
        $expiredRentals = $this->rentalRepository->createQueryBuilder('r')
            ->where('r.endDate <= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        if (empty($expiredRentals)) {
            $io->success('Tout est à jour. Aucune location n\'a expiré.');
            return Command::SUCCESS;
        }

        $renewed = 0;
        $canceled = 0;

        foreach ($expiredRentals as $rental) {
            if ($rental->isAutoRenew()) {
                // CAS 1 : Le client a laissé le renouvellement automatique
                // On repousse la date de fin de 30 jours
                $newEndDate = (clone $rental->getEndDate())->modify('+30 days');
                $rental->setEndDate($newEndDate);
                $renewed++;
            } else {
                // CAS 2 : Le client a annulé son abonnement
                // On libère les serveurs (Unités) pour qu'ils retournent dans le Datacenter
                foreach ($rental->getUnits() as $unit) {
                    $unit->setRental(null);
                    $unit->setDescription('Unité disponible');
                }

                // On supprime la location (le contrat est terminé)
                $this->em->remove($rental);
                $canceled++;
            }
        }

        // On exécute toutes les modifications en BDD d'un seul coup
        $this->em->flush();

        $io->success(sprintf('Opération terminée : %d abonnements renouvelés, %d abonnements annulés.', $renewed, $canceled));

        return Command::SUCCESS;
    }
}
