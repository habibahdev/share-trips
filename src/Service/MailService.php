<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail,
        private readonly string $fromName
    ) {
    }

    public function sendWelcome(User $user): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject('Bienvenue sur ShareTrips')
            ->htmlTemplate('emails/welcome.html.twig')
            ->context([
                'user' => $user,
                'token' => $user->getTokenRegister(),
                'lifetimeToken' => $user->getTokenRegisterLifetime()->format('d/m/Y H:i:s')
            ])
        ;
        $this->send($email);
    }

    public function sendBookingConfirmation(Booking $booking): void
    {
        $passenger = $booking->getPassenger();
        $email = (new TemplatedEmail())
            ->to(new Address($passenger->getEmail(), $passenger->getFullName()))
            ->subject('Réservation enregistrée - ShareTrips')
            ->htmlTemplate('emails/booking_confirmation.html.twig')
            ->context(['booking' => $booking])
        ;
        $this->send($email);
    }

    public function sendBookingApproved(Booking $booking): void
    {
        $passenger = $booking->getPassenger();
        $email = (new TemplatedEmail())
            ->to(new Address($passenger->getEmail(), $passenger->getFullName()))
            ->subject('Réservation confirmée - ShareTrips')
            ->htmlTemplate('emails/booking_approved.html.twig')
            ->context(['booking' => $booking])
        ;
        $this->send($email);
    }

    public function sendSuspension(User $user): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject('Compte suspendu - ShareTrips')
            ->htmlTemplate('emails/suspension.html.twig')
            ->context(['user' => $user])
        ;
        $this->send($email);
    }

    public function sendBan(User $user): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject('Compte banni - ShareTrips')
            ->htmlTemplate('emails/ban.html.twig')
            ->context(['user' => $user])
        ;
        $this->send($email);
    }

    public function sendNewBookingToDriver(Booking $booking): void
    {
        $driver = $booking->getTrip()->getDriver();
        $email = (new TemplatedEmail())
            ->to(new Address($driver->getEmail(), $driver->getFullName()))
            ->subject('Nouvelle demande de réservation - ShareTrips')
            ->htmlTemplate('emails/new_booking_driver.html.twig')
            ->context(['booking' => $booking])
        ;
        $this->send($email);
    }

    public function sendBookingCancellationToDriver(Booking $booking): void
    {
        $driver = $booking->getTrip()->getDriver();
        $email = (new TemplatedEmail())
            ->to(new Address($driver->getEmail(), $driver->getFullName()))
            ->subject('Annulation de réservation - ShareTrips')
            ->htmlTemplate('emails/booking_cancellation_driver.html.twig')
            ->context(['booking' => $booking])
        ;
        $this->send($email);
    }

    public function sendTripCancellationToPassanger(Booking $booking): void
    {
        $passenger = $booking->getPassenger();
        $email = (new TemplatedEmail())
            ->to(new Address($passenger->getEmail(), $passenger->getFullName()))
            ->subject('Trajet annulé - ShareTrips')
            ->htmlTemplate('emails/trip_cancellation_passenger.html.twig')
            ->context(['booking' => $booking])
        ;
        $this->send($email);
    }

    public function sendRefund(Booking $booking): void
    {
        $passenger = $booking->getPassenger();
        $email = (new TemplatedEmail())
            ->to(new Address($passenger->getEmail(), $passenger->getFullName()))
            ->subject('Remboursement en cours - ShareTrips')
            ->htmlTemplate('emails/refund.html.twig')
            ->context(['booking' => $booking])
        ;
        $this->send($email);
    }

    private function send(TemplatedEmail $email): void
    {
        $email->from(new Address($this->fromEmail, $this->fromName));
        $this->mailer->send($email);
    }
}
