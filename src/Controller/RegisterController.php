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

    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            $existingUser = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if ($existingUser && $existingUser->isVerified() === true) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('register/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
            if ($existingUser && $existingUser->isVerified() === false) {
                $this->addFlash('error', 'Cet email est déjà utilisé mais pas vérifié');
                return $this->redirectToRoute('app_check_email');
            }

            $user->setRoles(["ROLE_USER"]);
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

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

            return $this->redirectToRoute('app_check_email');
        }


        return $this->render('register/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email/{id}', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        EntityManagerInterface $entityManager,
        int $id
    ): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (null === $user) {
            $this->addFlash('error', 'Utilisateur introuvable.'); // Ajouté
            return $this->redirectToRoute('app_register');
        }

        // On valide le lien crypté
        try {
                $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            // Si le lien est invalide ou expiré
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }

        // Si tout est bon, on valide l'utilisateur !
        $user->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Votre email a été vérifié ! Vous pouvez vous connecter.');
        return $this->redirectToRoute('app_security'); // Ou app_home
    }

    #[Route('/register/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('register/check_email.html.twig');
    }
}
