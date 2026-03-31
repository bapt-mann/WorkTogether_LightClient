<?php

namespace App\Repository;

use App\Entity\Rental;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rental>
 */
class RentalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rental::class);
    }

    /**
     * Récupérer les locations actives d'une entreprise avec toutes leurs relations (Unités, Baies, etc.)
     * afin d'éviter le problème de requêtes N+1 (Lazy Loading).
     */
    public function findActiveRentalsWithDetailsByCompany($company)
    {
        return $this->createQueryBuilder('r')
            // 1. On cherche les locations de l'entreprise qui sont actives
            ->where('r.company = :company')
            ->andWhere('r.isActive = true')
            ->setParameter('company', $company)

            // 2. LA MAGIE EST ICI : On fait des jointures et on "sélectionne" les données
            // (addSelect dit à Doctrine de garder les infos en mémoire)
            ->leftJoin('r.offer', 'o')
            ->addSelect('o')

            ->leftJoin('r.units', 'u')
            ->addSelect('u')

            ->leftJoin('u.bay', 'b')
            ->addSelect('b')

            ->leftJoin('u.state', 's')
            ->addSelect('s')

            ->getQuery()
            ->getResult();
    }
}
