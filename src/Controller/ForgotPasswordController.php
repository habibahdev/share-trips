<?php

namespace App\Controller;

use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur responsable du processus de mot de passe oublié.
 */
final class ForgotPasswordController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Affichage et traitement de la demande de réinitialisation.
     *
     * @param Request $request Requête HTTP
     * @param UserRepository $userRepository Repository des utilisateurs
     * @param MailService $mailer Service d'envoi d'emails
     * @return Response
     */
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function request(
        Request $request,
        UserRepository $userRepository,
        MailService $mailer
    ): Response {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);
            $this->addFlash(
                'info',
                'Si votre adresse email existe, vous recevrez un mail de réinitialisation de mot de passe.'
            );
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setTokenForgotPassword($token);
                $date = (new \DateTimeImmutable())->modify('+1 hour');
                $user->setTokenForgotPasswordExpiredAt($date);
                $this->entityManager->flush();
                $mailer->sendForgotPassword($user);
            }
            return $this->redirectToRoute('app_forgot_password_check');
        }
        return $this->render('forgot_password/request.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Information pour l'utilisateur.
     *
     * @return Response
     */
    #[Route('/forgot-password/check', name: 'app_forgot_password_check')]
    public function check(): Response
    {
        return $this->render('forgot_password/check.html.twig');
    }

    /**
     * Réinitialisation du mot de passe.
     *
     * @param string $token Token de réinitialisation
     * @param Request $request Requête HTTP
     * @param UserRepository $userRepository Repository des utilisateurs
     * @param UserPasswordHasherInterface $hasher Service du hachage du mot de passe
     * @return Response
     */
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(
        string $token,
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher
    ): Response {
        $user = $userRepository->findOneBy(['tokenForgotPassword' => $token]);
        if (!$user || !$user->isForgotPasswordTokenValid()) {
            $this->addFlash(
                'danger',
                'Ce lien est invlaide ou a expiré. Veuillez faire une nouvelle demande.'
            );
            return $this->redirectToRoute('app_forgot_password');
        }
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $newPassword));
            $user->setTokenForgotPassword(null);
            $user->setTokenForgotPasswordExpiredAt(null);
            $this->entityManager->flush();
            $this->addFlash(
                'success',
                'Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.'
            );
            return $this->redirectToRoute('app_login');
        }
        return $this->render('forgot_password/reset.html.twig', [
            'form' => $form,
            'token' => $token
        ]);
    }
}
