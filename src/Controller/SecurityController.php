<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Gérer l'authentification des utilisateurs.
 * Afficher le formulaire de connexion, intercepter les erreurs d'authentification,
 * et traiter le déblocage des comptes sécurisés.
 */
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, LoggerInterface $logger): Response
    {
        // Récupérer l'erreur de connexion si elle existe
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupérer le dernier identifiant saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Tracer la présence d'une erreur d'authentification ou la simple visite de la page
        if ($error) {
            $logger->warning('Échouer lors de la tentative de connexion.', [
                'username' => $lastUsername,
                'error_message' => $error->getMessageKey()
            ]);
        } else {
            $logger->info('Consulter la page de connexion.');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Laisser le composant de sécurité de Symfony intercepter cette route
        // Aucune logique supplémentaire n'est requise ici
    }

    #[Route(path: '/unlock/{id}/{token}', name: 'app_unlock_account')]
    public function unlockAccount(
        int $id,
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response
    {
        $logger->info('Initier la procédure de déblocage de compte.', ['user_id' => $id]);

        $user = $userRepository->find($id);

        // Valider l'existence de l'utilisateur, son statut de blocage et la validité du jeton
        if (!$user || !$user->isLocked() || $user->getUnlockToken() !== $token) {

            $logger->warning('Refuser le déblocage : lien invalide, expiré ou compte non bloqué.', [
                'user_id' => $id,
                'token_provided' => $token
            ]);

            $this->addFlash('error', 'Lien de déblocage invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }

        // Réinitialiser les sécurités et libérer l'accès au compte
        $user->setIsLocked(false);
        $user->setFailedLoginAttempts(0);
        $user->setUnlockToken(null);

        $em->flush();

        $logger->info('Débloquer le compte utilisateur avec succès.', ['user_id' => $id]);

        $this->addFlash('success', 'Votre compte a été débloqué avec succès. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}
