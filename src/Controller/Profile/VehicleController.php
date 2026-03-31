<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Form\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VehicleController extends AbstractController
{
    #[Route('/profile/vehicle', name: 'app_profile_vehicle')]
    public function index(): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        return $this->render('profile/vehicle/index.html.twig', [
            'vehicles' => $user->getVehicles(),
        ]);
    }

    #[Route('/profile/vehicle/form/{vehicle}', name: 'app_profile_vehicle_form', defaults: ['vehicle' => null])]
    public function form(?Vehicle $vehicle, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        if ($vehicle && $vehicle->getUsser() !== $user) {
            return $this->redirectToRoute('app_profile_vehicle');
        }
        if (!$vehicle) {
            $vehicle = new Vehicle();
            $vehicle->setUsser($user);
        }
        $form = $this->createForm(VehicleType::class, $vehicle);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vehicle);
            $entityManager->flush();
            $this->addFlash('success', 'Véhicule sauvegardé.');
            return $this->redirectToRoute('app_profile_vehicle');
        }
        return $this->render('profile/vehicle/form.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/profile/vehicle/delete/{vehicle}', name: 'app_profile_vehicle_delete', methods: ['POST'])]
    public function delete(Vehicle $vehicle, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
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
        $entityManager->remove($vehicle);
        $entityManager->flush();
        $this->addFlash('success', 'Véhicule supprimé.');
        return $this->redirectToRoute('app_profile_vehicle');
    }
}
