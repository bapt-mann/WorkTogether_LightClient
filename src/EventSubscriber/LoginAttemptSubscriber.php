<?php
namespace App\EventSubscriber;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginAttemptSubscriber //implements EventSubscriberInterface
{
    private const MAX_ATTEMPTS = 3;

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onLoginFailure',
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        // On récupère l'email tapé par l'utilisateur
        $email = $event->getPassport()->getUser()->getUserIdentifier();
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user) {
            $user->setFailedLoginAttempts($user->getFailedLoginAttempts() + 1);

            if ($user->getFailedLoginAttempts() >= self::MAX_ATTEMPTS) {
                $user->setIsLocked(true);
                // On génère un token aléatoire ultra-sécurisé de 64 caractères
                $user->setUnlockToken(bin2hex(random_bytes(32)));

                // ICI : Tu pourrais appeler ton Mailer pour envoyer le lien !
            }

            $this->em->flush();
        }
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if ($user instanceof \App\Entity\User) {
            // Si la connexion réussit, on remet le compteur à zéro
            $user->setFailedLoginAttempts(0);
            $this->em->flush();
        }
    }
}
