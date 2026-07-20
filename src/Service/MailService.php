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
        $this->send(
            to: new Address($user->getEmail(), $user->getFullName()),
            subject: 'Bienvenue sur ShareTrips',
            template: 'emails/welcome.html.twig',
            context: [
                'user' => $user,
                'token' => $user->getTokenRegister(),
                'lifetimeToken' => $user->getTokenRegisterLifetime()->format('d/m/Y H:i:s')
            ]
        );
    }

    public function sendBookingConfirmation(Booking $booking): void
    {
        $this->send(
            to: new Address($booking->getPassenger()->getEmail(), $booking->getPassenger()->getFullName()),
            subject: sprintf(
                'Paiement reçu - %s -> %s - ShareTrips',
                $booking->getTrip()->getOrigin(),
                $booking->getTrip()->getDestination()
            ),
            template: 'emails/booking_confirmation.html.twig',
            context: ['booking' => $booking]
        );
    }

    public function sendBookingApproved(Booking $booking): void
    {
        $this->send(
            to: new Address($booking->getPassenger()->getEmail(), $booking->getPassenger()->getFullName()),
            subject: sprintf(
                'Réservation confirmée - %s -> %s - ShareTrips',
                $booking->getTrip()->getOrigin(),
                $booking->getTrip()->getDestination()
            ),
            template: 'emails/booking_approved.html.twig',
            context: ['booking' => $booking]
        );
    }

    public function sendSuspension(User $user): void
    {
        $this->send(
            to: new Address($user->getEmail(), $user->getFullName()),
            subject: 'Compte suspendu - ShareTrips',
            template: 'emails/suspension.html.twig',
            context: ['user' => $user]
        );
    }

    public function sendBan(User $user): void
    {
        $this->send(
            to: new Address($user->getEmail(), $user->getFullName()),
            subject: 'Compte banni - ShareTrips',
            template: 'emails/ban.html.twig',
            context: ['user' => $user]
        );
    }

    public function sendNewBookingToDriver(Booking $booking): void
    {
        $this->send(
            to: new Address(
                $booking->getTrip()->getDriver()->getEmail(),
                $booking->getTrip()->getDriver()->getFullName()
            ),
            subject: 'Nouvelle demande de réservation - ShareTrips',
            template: 'emails/new_booking_driver.html.twig',
            context: ['booking' => $booking]
        );
    }

    public function sendBookingCancellationToDriver(Booking $booking): void
    {
        $this->send(
            to: new Address(
                $booking->getTrip()->getDriver()->getEmail(),
                $booking->getTrip()->getDriver()->getFullName()
            ),
            subject: 'Annulation de réservation - ShareTrips',
            template: 'emails/booking_cancellation_driver.html.twig',
            context: ['booking' => $booking]
        );
    }

    public function sendTripCancellationToPassenger(Booking $booking): void
    {
        $this->send(
            to: new Address($booking->getPassenger()->getEmail(), $booking->getPassenger()->getFullName()),
            subject: sprintf(
                'Trajet annulé -> %s -> %s - ShareTrips',
                $booking->getTrip()->getOrigin(),
                $booking->getTrip()->getDestination()
            ),
            template: 'emails/trip_cancellation_passenger.html.twig',
            context: ['booking' => $booking]
        );
    }

    public function sendRefund(Booking $booking): void
    {
        $this->send(
            to: new Address($booking->getPassenger()->getEmail(), $booking->getPassenger()->getFullName()),
            subject: sprintf('Remboursement de %.2f€ initié - ShareTrips', $booking->getTotalPrice()),
            template: 'emails/refund.html.twig',
            context: ['booking' => $booking]
        );
    }

    public function sendForgotPassword(User $user): void
    {
        $this->send(
            to: new Address($user->getEmail(), $user->getFullName()),
            subject: 'Réinitialisation de votre mot de passe - ShareTrips',
            template: 'emails/forgot_password.html.twig',
            context: [
                'user' => $user,
                'token' => $user->getTokenForgotPassword(),
                'expiredAt' => $user->getTokenForgotPasswordExpiredAt()->format('d/m/Y à H:i')
            ]
        );
    }

    public function sendIdentityVerified(User $user): void
    {
        $this->send(
            to: new Address($user->getEmail(), $user->getFullName()),
            subject: 'Identité vérifiée - ShareTrips',
            template: 'emails/identity_verified.html.twig',
            context: [
                'user' => $user
            ]
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function send(Address $to, string $subject, string $template, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context)
        ;
        $this->mailer->send($email);
    }
}
