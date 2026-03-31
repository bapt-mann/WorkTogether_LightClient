<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Form\FormError;
use Psr\Log\LoggerInterface; // Importation du Logger

/**
 * Gérer la page d'inscription de l'application.
 * Permettre aux utilisateurs de créer un compte via un formulaire.
 * Valider les données, instancier un nouvel utilisateur, et transmettre un email de confirmation sécurisé.
 */
final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer,
        UserRepository $userRepository,
        LoggerInterface $logger
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $logger->info('Initier le processus d\'inscription pour un nouvel utilisateur.');

            $plainPassword = $form->get('plainPassword')->getData();

            // Valider la complexité du mot de passe
            // Critères : 8 caractères minimum, 2 majuscules, 2 minuscules, 2 chiffres, 1 caractère spécial
            $regex = '/^(?=(.*[a-z]){2})(?=(.*[A-Z]){2})(?=(.*\d){2})(?=.*[^a-zA-Z0-9]).{8,}$/';

            if (!preg_match($regex, $plainPassword)) {
                $logger->warning('Échouer lors de l\'inscription : format de mot de passe invalide.');

                $form->get('plainPassword')->get('first')->addError(new FormError(
                    'Le mot de passe doit contenir au moins 8 caractères, dont 2 minuscules, 2 majuscules, 2 chiffres et 1 caractère spécial.'
                ));
                return $this->render('register/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Hacher et assigner le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            // Vérifier l'existence préalable de l'email en base de données
            $emailSaisi = $form->get('email')->getData();
            $existingUser = $userRepository->findOneBy(['email' => $emailSaisi]);

            if ($existingUser && $existingUser->isVerified() === true) {
                $logger->warning('Refuser l\'inscription : tentative d\'utilisation d\'un email déjà vérifié.', [
                    'email_input' => $emailSaisi
                ]);
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('register/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            if ($existingUser && $existingUser->isVerified() === false) {
                $logger->info('Rediriger l\'utilisateur : tentative d\'inscription avec un email en attente de vérification.', [
                    'email_input' => $emailSaisi
                ]);
                $this->addFlash('error', 'Cet email est déjà utilisé mais pas vérifié');
                return $this->redirectToRoute('app_check_email');
            }

            // Valider la saisie du nom de l'entreprise
            $companyName = $form->get('companyName')->getData();
            if ($companyName === null || trim($companyName) === '') {
                $logger->warning('Échouer lors de l\'inscription : nom d\'entreprise manquant.');
                $form->get('companyName')->addError(new FormError(
                    'Le nom de l\'entreprise est obligatoire.'
                ));
                return $this->render('register/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Nettoyer et valider le SIRET
            $rawSiret = trim($form->get('siret')->getData());
            $siret = str_replace(' ', '', (string) $rawSiret);

            if ($siret === '' || !preg_match('/^\d{14}$/', $siret)) {
                $logger->warning('Échouer lors de l\'inscription : format du SIRET invalide.', [
                    'siret_input' => $rawSiret
                ]);
                $form->get('siret')->addError(new FormError(
                    'Le SIRET de l\'entreprise est obligatoire et doit contenir 14 chiffres.'
                ));
                return $this->render('register/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            // Instancier l'entreprise et configurer l'utilisateur
            $company = new \App\Entity\Company();
            $company->setCompanyName($companyName);
            $company->setSiret($siret);

            $user->setCompany($company);
            $user->setRoles(["ROLE_USER"]);
            $user->setIsVerified(false);

            // Persister les nouvelles entités en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            $logger->info('Créer le compte utilisateur et l\'entreprise associée avec succès.', [
                'user_id' => $user->getId(),
                'company_id' => $company->getId()
            ]);

            // Générer et transmettre l'email de vérification sécurisé
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id'=> $user->getId()]
            );

            $email = (new Email())
                ->from('ne_pas_repondre@workTogether.fr')
                ->to($user->getEmail())
                ->subject('Confirmez votre email')
                ->html('<p>Merci de confirmer votre compte en cliquant ici : <a href="'.$signatureComponents->getSignedUrl().'">Valider mon compte</a></p>');

            $mailer->send($email);

            $logger->info('Transmettre l\'email de vérification.', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail()
            ]);

            return $this->redirectToRoute('app_check_email');
        }

        // Restituer le formulaire d'inscription
        return $this->render('register/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email/{id}', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        EntityManagerInterface $entityManager,
        int $id,
        LoggerInterface $logger
    ): Response
    {
        $logger->info('Initier la vérification du lien d\'email.', ['id' => $id]);

        $user = $entityManager->getRepository(User::class)->find($id);

        if (null === $user) {
            $logger->error('Échouer lors de la vérification : utilisateur introuvable.', ['id' => $id]);
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_register');
        }

        // Valider l'intégrité du lien crypté
        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            // Traiter le cas d'un lien invalide ou expiré
            $logger->warning('Refuser la vérification : lien invalide ou expiré.', [
                'user_id' => $user->getId(),
                'reason' => $e->getReason()
            ]);
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }

        // Confirmer la vérification de l'utilisateur
        $user->setIsVerified(true);
        $entityManager->flush();

        $logger->info('Valider l\'adresse email de l\'utilisateur avec succès.', [
            'user_id' => $user->getId()
        ]);

        $this->addFlash('success', 'Votre email a été vérifié ! Vous pouvez vous connecter.');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/register/check-email', name: 'app_check_email')]
    public function checkEmail(LoggerInterface $logger): Response
    {
        $logger->info('Consulter la page d\'attente de vérification d\'email.');
        return $this->render('register/check_email.html.twig');
    }
}
