<?php

namespace App\Command;

use App\Repository\RentalRepository;
use App\Repository\UnitRentalHistoRepository;
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
        private UnitRentalHistoRepository $unitRentalHistoRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();

        // 1. NOUVELLE REQUÊTE : On cherche les locations ACTIVES dont la date est dépassée
        $expiredRentals = $this->rentalRepository->createQueryBuilder('r')
            ->where('r.endDate <= :now')
            ->andWhere('r.isActive = :active') // <-- On filtre sur les locations actives
            ->setParameter('now', $now)
            ->setParameter('active', true)
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
                // Le client a laissé le renouvellement automatique
                $duration = $rental->getOffer()->getDurationInDay();
                $newEndDate = (clone $rental->getEndDate())->modify('+' . $duration . ' days');
                $rental->setEndDate($newEndDate);
                $renewed++;
            } else {
                // Le client a annulé son abonnement, on libère les serveurs
                foreach ($rental->getUnits() as $unit) {
                    $unit->setRental(null);
                    $unit->setDescription('Unité disponible');

                    $histo = $this->unitRentalHistoRepository->createQueryBuilder('u')
                        ->where('u.unit = :unit')
                        ->andWhere('u.rental = :rental')
                        ->setParameter('unit', $unit)
                        ->setParameter('rental', $rental)
                        ->getQuery()
                        ->getOneOrNullResult();

                    if ($histo) {
                        $histo->setEndDate($now);
                    }
                }

                // 2. LA MAGIE DE L'ARCHIVAGE EST ICI
                // Fini le $this->em->remove($rental); ! On désactive simplement la location :
                $rental->setIsActive(false);

                $canceled++;
            }
        }

        // On exécute toutes les modifications en BDD d'un seul coup
        $this->em->flush();

        $io->success(sprintf('Opération terminée : %d abonnements renouvelés, %d abonnements archivés.', $renewed, $canceled));

        return Command::SUCCESS;
    }
}
