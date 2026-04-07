<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur responsable de l'authentification des utilisateurs.
 */
final class LoginController extends AbstractController
{
    /**
     * Affiche formulaire de connexion.
     *
     * @param AuthenticationUtils $authenticationUtils Outils Symfony pour l'authentification
     * @return Response
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * Point d'entrée pour la déconnexion.
     *
     * @return never
     * @throws \LogicException Toujours levée si méthode appelée directement
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method should not be reached.');
    }
}
