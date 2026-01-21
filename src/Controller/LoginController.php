<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response
    {
        $user = new User();
        $form = $this->createForm(LoginFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user->getPassword() && !$user->getEmail()){
                $this->addFlash('error', 'champs invalides');
                return $this->redirectToRoute('app_login');
            }

            $passwordAttempt = $userPasswordHasher->hashPassword($user, $user->getPassword());
            $mail = $user->getEmail();

            $existUser = $userRepository->findOneBy(['email' => $mail]);

            if ($existUser && password_verify($passwordAttempt, $existUser->getPassword())) {
                $this->addFlash('success','vous etes connecté');
                return $this->redirectToRoute('app_home');
            }
            else if ($existUser) {
                $this->addFlash('error', 'mauvais email ou mot de passe');
                return $this->redirectToRoute('app_login');
            }
            else {
                $this->addFlash('error', 'aucun utilisateur correspondant à cet email');
                return $this->redirectToRoute('app_login');
            }

        }
        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'loginForm' => $form->createView(),
        ]);
    }
}
