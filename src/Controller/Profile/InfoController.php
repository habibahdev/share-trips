<?php

namespace App\Controller\Profile;

use App\Form\InfoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InfoController extends AbstractController
{
    #[Route('/profile/info', name: 'app_profile_info')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(InfoType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Informations enregistrées.');
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('profile/info/index.html.twig', [
            'form' => $form,
        ]);
    }
}
