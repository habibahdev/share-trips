<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Form\IdentityDocumentType;
use App\Service\IdentityDocumentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile/identity', name: 'app_profile_identity_')]
final class IdentityController extends AbstractController
{
    public function __construct(private readonly IdentityDocumentService $identityService)
    {
    }

    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($user->isIdentityVerified()) {
            $this->addFlash('info', 'Votre identité est déjà vérifiée.');
            return $this->redirectToRoute('app_profile');
        }

        $form = $this->createForm(IdentityDocumentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('identityDocument')->getData();
            try {
                $this->identityService->delete($user->getIdentityDocument());
                $filename = $this->identityService->store($file);
                $user->setIdentityDocument($filename);
                // repasser en attente si un nouveau document a été soumis
                $user->setIdentityDocument($filename);
                $user->setIdentityVerifiedAt(null);
                $em->flush();
                $this->addFlash(
                    'success',
                    'Document envoyé. Notre équipe va vérifier notre identité sous 24h.'
                );
                return $this->redirectToRoute('app_profile');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }
        return $this->render('profile/identity/index.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }
}
