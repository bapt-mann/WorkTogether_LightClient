<?php
namespace App\EventSubscriber;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Écouter les événements liés aux tentatives de connexion.
 * Sécuriser les comptes utilisateurs en limitant les échecs d'authentification.
 */
class LoginAttemptSubscriber implements EventSubscriberInterface
{
    private const MAX_ATTEMPTS = 3;

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
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
        // Extraire l'identifiant fourni lors de la tentative échouée
        $email = $event->getPassport()->getUser()->getUserIdentifier();
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user) {
            // Incrémenter le compteur de tentatives de connexion échouées
            $user->setFailedLoginAttempts($user->getFailedLoginAttempts() + 1);

            $this->logger->warning('Enregistrer un échec de connexion pour un utilisateur existant.', [
                'user_email' => $email,
                'failed_attempts' => $user->getFailedLoginAttempts()
            ]);

            // Vérifier si le seuil maximum d'échecs est atteint
            if ($user->getFailedLoginAttempts() >= self::MAX_ATTEMPTS) {

                // Verrouiller le compte pour prévenir les attaques par force brute
                $user->setIsLocked(true);

                // Générer un jeton de déblocage aléatoire et hautement sécurisé
                $unlockToken = bin2hex(random_bytes(32));
                $user->setUnlockToken($unlockToken);

                $this->logger->critical('Verrouiller le compte utilisateur suite à de multiples échecs d\'authentification.', [
                    'user_id' => $user->getId(),
                    'user_email' => $email
                ]);

                $unlockUrl = $this->urlGenerator->generate(
                    'app_unlock_account',
                    ['id' => $user->getId(), 'token' => $unlockToken],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                // Transmettre un email contenant le lien de déblocage
                $emailMessage = (new Email())
                    ->from('ne_pas_repondre@workTogether.fr')
                    ->to($user->getEmail())
                    ->subject('Débloquez votre compte')
                    ->html('<p>Votre compte a été bloqué suite à plusieurs tentatives échouées.</p><p>Débloquez votre compte en cliquant ici : <a href="'.$unlockUrl.'">Débloquer mon compte</a></p>');

                $this->mailer->send($emailMessage);

                $this->logger->info('Transmettre l\'email de déblocage.', [
                    'user_id' => $user->getId(),
                    'email' => $user->getEmail()
                ]);
            }

            // Persister les modifications sécuritaires en base de données
            $this->em->flush();
        } else {
            // Tracer les tentatives sur des adresses email inexistantes pour détecter un potentiel balayage
            $this->logger->warning('Enregistrer un échec de connexion avec un email inexistant.', [
                'email_input' => $email
            ]);
        }
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if ($user instanceof \App\Entity\User) {

            // Réinitialiser le compteur d'échecs suite à une authentification valide
            $user->setFailedLoginAttempts(0);
            $this->em->flush();

            $this->logger->info('Authentifier l\'utilisateur avec succès et réinitialiser les compteurs de sécurité.', [
                'user_id' => $user->getId()
            ]);
        }
    }
}
