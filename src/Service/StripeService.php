<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\Payment;
use Stripe\Checkout\Session;
use Stripe\Refund;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class StripeService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function createCheckoutSession(Booking $booking, Payment $payment): Session
    {
        $trip = $booking->getTrip();
        return Session::create([
            'mode' => 'payment',
            'currency' => 'eur',
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) round($payment->getAmount() * 100),
                    'product_data' => [
                        'name' => sprintf(
                            'Trajet %s -> %s le %s',
                            $trip->getOrigin(),
                            $trip->getDestination(),
                            $trip->getDepartureAt()->format('d/m/Y H:i')
                        ),
                        'description' => sprintf(
                            '%d place(s) x %.2f €',
                            $booking->getSeatsBooked(),
                            $trip->getPricePerSeat()
                        ),
                    ],
                ],
            ]],

            'metadata' => [
                'payment_id' => (string) $payment->getId(),
                'booking_id' => (string) $booking->getId()
            ],

            'success_url' => $this->urlGenerator->generate(
                'app_booking_stripe_success',
                ['booking' => $booking->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),

            'cancel_url' => $this->urlGenerator->generate(
                'app_booking_stripe_cancel',
                ['booking' => $booking->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }

    public function refund(Payment $payment): Refund
    {
        $session = Session::retrieve($payment->getStripeSessionId());
        return Refund::create([
            'payment_intent' => $session->payment_intent
        ]);
    }
}
