<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\IdentityDocumentService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractCrudController<User>
 */
#[Route('/admin/identity', name: 'admin_identity_')]
class IdentityCrudController extends AbstractCrudController
{
    public function __construct(private readonly IdentityDocumentService $identityService)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[Route('/document/{id}', name: 'document')]
    public function document(User $user): Response
    {
        if (!$user->getIdentityDocument()) {
            throw $this->createNotFoundException('Aucun document pour cet utilisateur.');
        }

        $path = $this->identityService->getPath($user->getIdentityDocument());

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Fichier introuvable.');
        }

        // Servi inline (preview PDF/image) sans révéler le vrai nom du fichier
        return new BinaryFileResponse($path, 200, [
            'Content-Disposition' => 'inline; filename="identity_document"',
        ]);
    }
}
