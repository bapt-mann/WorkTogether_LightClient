<?php

namespace App\Scheduler;

use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as BaseSchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
class Schedule implements ScheduleProviderInterface
{
    public function getSchedule(): BaseSchedule
    {
        return (new BaseSchedule())->add(
        // On exécute la commande toutes les 10 secondes pour tester !
            RecurringMessage::every('10 seconds', new RunCommandMessage('app:check-rentals'))
        );
    }
}
