<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Entity\User;
use App\Form\RegisterType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

/**
 * Contrôleur responsable de l'inscription des utilisateurs.
 */
final class RegisterController extends AbstractAppController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Gère l'inscription.
     *
     * @param Request $request Requête HTTP
     * @param UserPasswordHasherInterface $hasher Service de hachage du mot de passe
     * @param MailService $mailer Service d'envoi e'mails
     * @param TokenGeneratorInterface $tokenGenerator Générateur de token sécurisé
     * @return Response
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        MailService $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $tokenRegister = $tokenGenerator->generateToken();
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setTokenRegister($tokenRegister);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $mailer->sendWelcome($user);
            $this->addFlash('info', 'Inscription prise en compte. Un e-mail de confirmation vous a été envoyé.');
            return $this->redirectToRoute('app_login');
        }
        return $this->render('register/index.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Confirmation de l'adresse l'e-mail d'un utilisateur via un token.
     *
     * @param string $token Token de validation
     * @param User $user Utilisateur
     * @return Response
     */
    #[Route('/verify/{token}/{id<\d+>}', name: 'app_confirm_email')]
    public function confirmEmail(string $token, User $user): Response
    {
        if ($user->isVerified()) {
            $this->addFlash('info', 'Ce compte est déjà activé.');
            return $this->redirectToRoute('app_login');
        }
        if ($user->getTokenRegister() === null || $user->getTokenRegister() !== $token) {
            $this->addFlash('danger', 'Lien de confirmation invalide.');
            return $this->redirectToRoute('app_register');
        }
        $lifetime = $user->getTokenRegisterLifetime();
        if ($lifetime !== null && new \DateTimeImmutable() > $lifetime) {
            $this->addFlash('danger', 'Ce lien a expiré. Veuillez en demander un nouveau.');
            return $this->redirectToRoute('app_resend');
        }
        $user->setIsVerified(true);
        $user->setTokenRegister(null);
        $this->entityManager->flush();
        $this->addFlash('success', 'Compte activé. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }

    /**
     * Renvoie un email de confirmation à l'utilisateur connecté.
     *
     * @param MailService $mailer Service d'envoi d'email
     * @param TokenGeneratorInterface $tokenGenerator Générateur de token sécurisé
     * @return Response
     */
    #[Route('/resend', name: 'app_resend')]
    public function resend(MailService $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        $user = $this->getAppUser();
        if ($user->isVerified()) {
            $this->addFlash('warning', 'Vous avez déjà vérifié votre adresse e-mail');
            return $this->redirectToRoute('app_profile');
        }
        $tokenRegister = $tokenGenerator->generateToken();
        $user->setTokenRegister($tokenRegister);
        $this->entityManager->flush();
        $mailer->sendWelcome($user);
        $this->addFlash('info', 'L\'e-mail de confirmation vous a été renvoyé.');
        return $this->redirectToRoute('app_profile');
    }
}
