<?php

namespace App\EventListener;

use App\Entity\Unit;
use App\Entity\UnitHisto;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

/**
 * Écouter les modifications sur l'entité Unit.
 * Générer automatiquement un journal d'audit (UnitHisto) à chaque mise à jour d'une unité.
 */
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Unit::class)]
class UnitHistoryListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function preUpdate(Unit $unit, PreUpdateEventArgs $args): void
    {
        // Extraire uniquement les champs modifiés avec leurs anciennes et nouvelles valeurs
        $changeset = $args->getEntityChangeSet();

        // Interrompre le processus si aucune modification réelle n'est détectée
        if (empty($changeset)) {
            return;
        }

        $this->logger->info('Générer une entrée d\'historique automatique pour une unité.', [
            'unit_id' => $unit->getId(),
            'champs_modifies' => array_keys($changeset)
        ]);

        $em = $args->getObjectManager();

        // Instancier une nouvelle entrée pour le journal d'audit
        $histo = new UnitHisto();
        $histo->setUnit($unit);
        $histo->setUpdateDate(new \DateTime());
        $histo->setState($unit->getState());

        if ($unit->getRental()) {
            $histo->setRental($unit->getRental());
        }

        $histo->setUnitDescription($unit->getDescription() ?? 'Aucune description');

        // Convertir le tableau des modifications au format JSON pour le stockage
        $histo->setChanges(json_encode($changeset));

        // Persister la nouvelle entité d'historique
        $em->persist($histo);

        // Forcer la prise en compte de cette nouvelle entité par l'unité de travail de Doctrine pendant l'événement de mise à jour
        $uow = $em->getUnitOfWork();
        $meta = $em->getClassMetadata(UnitHisto::class);
        $uow->computeChangeSet($meta, $histo);
    }
}
