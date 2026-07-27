<?php

namespace App\Controller\Profile;

use App\Controller\AbstractAppController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractAppController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getAppUser();
        return $this->render('profile/home/index.html.twig', [
            'user' => $user
        ]);
    }
}
