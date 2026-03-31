<?php
namespace App\Security;

use App\Entity\User;
use Psr\Log\LoggerInterface; // Importation du Logger
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Vérifier l'état du compte utilisateur lors du processus d'authentification.
 * Interdire l'accès aux utilisateurs dont le compte est verrouillé.
 */
class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private LoggerInterface $logger // Injection du Logger
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isLocked()) {
            // Tracer la tentative de connexion frauduleuse ou bloquée
            $this->logger->warning('Refuser l\'authentification : tentative de connexion sur un compte bloqué.', [
                'user_id' => $user->getId(),
                'user_email' => $user->getEmail()
            ]);

            // Lever une exception pour interrompre l'authentification et alerter l'utilisateur
            throw new CustomUserMessageAccountStatusException('Votre compte est bloqué suite à trop de tentatives. Vérifiez vos emails.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Aucune validation supplémentaire post-authentification n'est requise pour le moment
    }
}
