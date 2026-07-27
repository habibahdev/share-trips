<?php

namespace App\Controller\Profile;

use App\Controller\AbstractAppController;
use App\Entity\Vehicle;
use App\Form\VehicleType;
use App\Security\VehicleVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile/vehicle', name: 'app_profile_vehicle')]
final class VehicleController extends AbstractAppController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: '')]
    public function index(): Response
    {
        $user = $this->getAppUser();
        return $this->render('profile/vehicle/index.html.twig', [
            'vehicles' => $user->getVehicles(),
        ]);
    }

    #[Route('/form/{vehicle}', name: '_form', defaults: ['vehicle' => null])]
    public function form(?Vehicle $vehicle, Request $request): Response
    {
        $user = $this->getAppUser();
        if ($vehicle) {
            $this->denyAccessUnlessGranted(VehicleVoter::EDIT, $vehicle);
        }
        if (!$vehicle) {
            $vehicle = new Vehicle();
            $vehicle->setUsser($user);
        }
        $form = $this->createForm(VehicleType::class, $vehicle);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($vehicle);
            $this->entityManager->flush();
            $this->addFlash('success', 'Véhicule sauvegardé.');
            return $this->redirectToRoute('app_profile_vehicle');
        }
        return $this->render('profile/vehicle/form.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/profile/vehicle/delete/{vehicle}', name: '_delete', methods: ['POST'])]
    public function delete(Vehicle $vehicle, Request $request): Response
    {
        $user = $this->getAppUser();
        $this->denyAccessUnlessGranted(VehicleVoter::EDIT, $vehicle);

        if (
            !$this->isCsrfTokenValid(
                'delete_vehicle_' . $vehicle->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Problème inconnu.');
            return $this->redirectToRoute('app_profile_vehicle');
        }
        if ($vehicle->getUsser() !== $user) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer ce véhicule.');
            return $this->redirectToRoute('app_profile_vehicle');
        }
        foreach ($vehicle->getTrips() as $trip) {
            if ($trip->getDepartureAt() > new \DateTimeImmutable()) {
                $this->addFlash('danger', 'Impossible de supprimer un véhicule associé à un trajet à venir.');
                return $this->redirectToRoute('app_profile_vehicle');
            }
        }
        $this->entityManager->remove($vehicle);
        $this->entityManager->flush();
        $this->addFlash('success', 'Véhicule supprimé.');
        return $this->redirectToRoute('app_profile_vehicle');
    }
}
