<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller de la page de connexion et de déconnexion
 * Permet aux utilisateurs de se connecter à leur compte en utilisant leur adresse email et mot de passe
 * Affiche les erreurs de connexion si les informations sont incorrectes
 * La déconnexion est gérée par Symfony, il suffit d'avoir une route dédiée pour que le système fonctionne
 */
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
    }

    #[Route(path: '/unlock/{id}/{token}', name: 'app_unlock_account')]
    public function unlockAccount(int $id, string $token, \App\Repository\UserRepository $userRepository, \Doctrine\ORM\EntityManagerInterface $em): \Symfony\Component\HttpFoundation\Response
    {
        $user = $userRepository->find($id);

        // On vérifie que l'utilisateur existe, qu'il est bien bloqué, et que le token correspond
        if (!$user || !$user->isLocked() || $user->getUnlockToken() !== $token) {
            $this->addFlash('error', 'Lien de déblocage invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }

        // On libère l'utilisateur
        $user->setIsLocked(false);
        $user->setFailedLoginAttempts(0);
        $user->setUnlockToken(null);

        $em->flush();

        $this->addFlash('success', 'Votre compte a été débloqué avec succès. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}
