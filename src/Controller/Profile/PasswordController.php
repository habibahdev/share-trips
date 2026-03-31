<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PasswordController extends AbstractController
{
    #[Route('/profile/password', name: 'app_profile_password')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        assert($user instanceof User);
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $actualPassword = $form->get('actualPassword')->getData();
            if ($hasher->isPasswordValid($user, $actualPassword)) {
                $newPassword = $form->get('newPassword')->getData();
                $user->setPassword($hasher->hashPassword($user, $newPassword));
                $entityManager->flush();
                $this->addFlash('success', 'Mot de passe modifié.');
                return $this->redirectToRoute('app_profile');
            }
            $this->addFlash('danger', 'Le mot de passe actuel est incorrect.');
        }
        return $this->render('profile/password/index.html.twig', [
            'form' => $form,
        ]);
    }
}
