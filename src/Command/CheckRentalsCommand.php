<?php

namespace App\Command;

use App\Repository\RentalRepository;
use App\Repository\UnitRentalHistoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface; // Importation du Logger
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-rentals',
    description: 'Vérifier les locations expirées pour les renouveler ou libérer les serveurs.',
)]
class CheckRentalsCommand extends Command
{
    public function __construct(
        private RentalRepository $rentalRepository,
        private UnitRentalHistoRepository $unitRentalHistoRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger // Injection du Logger via le constructeur
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();

        $this->logger->info('Démarrer la tâche planifiée de vérification des locations expirées.');

        // Rechercher les locations actives dont la date de fin est dépassée
        $expiredRentals = $this->rentalRepository->createQueryBuilder('r')
            ->where('r.endDate <= :now')
            ->andWhere('r.isActive = :active')
            ->setParameter('now', $now)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        if (empty($expiredRentals)) {
            $this->logger->info('Terminer la tâche : aucune location n\'a expiré.');
            $io->success('Tout est à jour. Aucune location n\'a expiré.');
            return Command::SUCCESS;
        }

        $renewed = 0;
        $canceled = 0;

        foreach ($expiredRentals as $rental) {
            if ($rental->isAutoRenew()) {
                // Prolonger automatiquement la location selon la durée prévue par l'offre
                $duration = $rental->getOffer()->getDurationInDays(); // Attention : vérifie si ta méthode s'appelle getDurationInDays ou getDurationInDay
                $newEndDate = (clone $rental->getEndDate())->modify('+' . $duration . ' days');
                $rental->setEndDate($newEndDate);
                $renewed++;

                $this->logger->info('Renouveler automatiquement une location.', [
                    'rental_id' => $rental->getId(),
                    'company_id' => $rental->getCompany()->getId(),
                    'new_end_date' => $newEndDate->format('Y-m-d H:i:s')
                ]);
            } else {
                // Libérer les serveurs suite à l'annulation du renouvellement
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

                // Archiver la location en la désactivant
                $rental->setIsActive(false);
                $canceled++;

                $this->logger->info('Archiver une location et libérer les serveurs associés.', [
                    'rental_id' => $rental->getId(),
                    'company_id' => $rental->getCompany()->getId()
                ]);
            }
        }

        // Persister toutes les modifications en base de données de manière transactionnelle
        $this->em->flush();

        $this->logger->info('Clôturer la tâche de vérification avec succès.', [
            'renewed_count' => $renewed,
            'canceled_count' => $canceled
        ]);

        $io->success(sprintf('Opération terminée : %d abonnements renouvelés, %d abonnements archivés.', $renewed, $canceled));

        return Command::SUCCESS;
    }
}
