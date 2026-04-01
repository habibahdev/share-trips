<?php

namespace App\Controller\Admin;

use App\Repository\BookingRepository;
use App\Repository\ReportRepository;
use App\Repository\ReviewRepository;
use App\Repository\TripRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UserRepository $userRepository,
        private TripRepository $tripRepository,
        private BookingRepository $bookingRepository,
        private ReportRepository $reportRepository,
        private ReviewRepository $reviewRepository
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $this->userRepository->count([]),
            'totalTrips' => $this->tripRepository->count([]),
            'totalBookings' => $this->bookingRepository->count([]),
            'pendingReports' => $this->reportRepository->count(['status' => 'pending']),
            'recentReports' => $this->reportRepository->findBy(['status' => 'pending'], ['createdAt' => 'desc'], 5),
            'bannedUsers' => $this->userRepository->count(['status' => 'banned']),
            'suspendedUsers' => $this->userRepository->count(['status' => 'suspended']),
            'totalReviews' => $this->reviewRepository->count([])
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Share Trips');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Gestion');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users');
        yield MenuItem::linkTo(ReportCrudController::class, 'Signalements', 'fa fa-flag');
        yield MenuItem::linkTo(PaymentCrudController::class, 'Paiements', 'fa fa-coins');
        yield MenuItem::linkTo(ReviewCrudController::class, 'Avis', 'fa fa-comments');
    }
}
