<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $entityManager,
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
            $entityManager->persist($user);
            $entityManager->flush();
            $mailer->sendWelcome($user);
            $this->addFlash('info', 'Inscription prise en compte. Un e-mail de confirmation vous a été evnoyé.');
        }
        return $this->render('register/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verify/{token}/{id<\d+>}', name: 'app_confirm_email', methods: ['GET'])]
    public function confirmEmail(
        string $token,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        if ($user->getTokenRegister() !== $token) {
        }
        if ($user->getTokenRegister() === null) {
        }
        if (new \DateTime() > $user->getTokenRegisterLifetime()) {
        }
        $user->setIsVerified(true);
        $user->setTokenRegister(null);
        $entityManager->flush();
        $this->addFlash('success', 'Compte activé. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/resend', name: 'app_resend')]
    public function resend(
        EntityManagerInterface $entityManager,
        MailService $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($user->isVerified()) {
            $this->addFlash('warning', 'Vous avez déjà vérifié votre adresse e-mail');
            return $this->redirectToRoute('app_profile');
        }
        $tokenRegister = $tokenGenerator->generateToken();
        $user->setTokenRegister($tokenRegister);
        $entityManager->persist($user);
        $entityManager->flush();
        $mailer->sendWelcome($user);
        $this->addFlash('info', 'L\'e-mail de confirmation vous a été renvoyé.');
        return $this->redirectToRoute('app_profile');
    }
}
