<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isLocked()) {
            // Le message qui s'affichera sur la page de connexion
            throw new CustomUserMessageAccountStatusException('Votre compte est bloqué suite à trop de tentatives. Vérifiez vos emails.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // On ne fait rien ici
    }
}
