<?php

namespace App\Scheduler;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as BaseSchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/**
 * Définir les tâches planifiées de l'application.
 * Automatiser l'exécution des commandes de maintenance en arrière-plan.
 */
#[AsSchedule('default')]
class Schedule implements ScheduleProviderInterface
{
    public function getSchedule(): BaseSchedule
    {
        return (new BaseSchedule())->add(
        // Exécuter la commande de vérification des locations tous les jours à minuit
        // (Utilisation de la syntaxe CRON pour plus de précision en production)
            RecurringMessage::every('10 seconds', new RunCommandMessage('app:check-rentals'))
        );
    }
}
