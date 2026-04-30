<?php

namespace App\Controller;

use App\Enum\BookingStatus;
use App\Repository\PaymentRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class StripeWebhookController extends AbstractController
{
    public function __construct(
        private string $webhookSecret,
        private string $secretKey
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    public function handle(
        Request $request,
        PaymentRepository $paymentRepository,
        EntityManagerInterface $entityManager,
        MailService $mailer
    ): JsonResponse {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');
        try {
            $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        } catch (SignatureVerificationException) {
            return new JsonResponse(['error' => 'Signature invalide'], Response::HTTP_BAD_REQUEST);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->onCheckoutCompleted(
                $event,
                $paymentRepository,
                $entityManager,
                $mailer
            ),
            'checkout.session.expired' => $this->onCheckoutExpired(
                $event,
                $paymentRepository,
                $entityManager
            ),
            default => null,
        };
        return new JsonResponse(['status' => 'ok']);
    }

    private function onCheckoutCompleted(
        Event $event,
        PaymentRepository $paymentRepository,
        EntityManagerInterface $entityManager,
        MailService $mailer
    ): void {
        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;

        /** @var array<string, string> $metadata */
        $metadata = (array) $session->metadata;

        $paymentId = (int) ($metadata['payment_id'] ?? 0);
        $payment = $paymentRepository->find($paymentId);
        if (!$payment || $payment->getStatus()->isFinal()) {
            return;
        }

        $payment->markAsCompleted();
        $payment->getBooking()->setStatus(BookingStatus::Pending);
        $entityManager->flush();
        $mailer->sendBookingConfirmation($payment->getBooking());
        $mailer->sendNewBookingToDriver($payment->getBooking());
    }

    private function onCheckoutExpired(
        Event $event,
        PaymentRepository $paymentRepository,
        EntityManagerInterface $entityManager
    ): void {
        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;

        /** @var array<string, string> $metadata */
        $metadata = (array) $session->metadata;

        $paymentId = (int) ($metadata['payment_id'] ?? 0);
        $payment = $paymentRepository->find($paymentId);
        if (!$payment || $payment->getStatus()->isFinal()) {
            return;
        }
        $payment->markAsFailed();
        $payment->getBooking()->setStatus(BookingStatus::Cancelled);
        $entityManager->flush();
    }
}
