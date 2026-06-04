<?php

namespace App\Service;

use Stripe\Stripe;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class StripeInitializer
{
    public function __construct(private readonly string $secretKey)
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        Stripe::setApiKey($this->secretKey);
    }
}
