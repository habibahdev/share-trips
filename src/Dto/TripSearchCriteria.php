<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\Request;

final class TripSearchCriteria
{
    public function __construct(
        public readonly ?string $origin,
        public readonly ?string $destination,
        public readonly ?\DateTimeImmutable $date
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $origin = $request->query->get('origin');
        $destination = $request->query->get('destination');
        $dateString = $request->query->get('date');

        $date = null;
        if ($dateString) {
            try {
                $date = new \DateTimeImmutable($dateString);
            } catch (\Exception) {
                $date = null;
            }
        }

        return new self($origin, $destination, $date);
    }

    public function isEmpty(): bool
    {
        return !$this->origin && !$this->destination && !$this->date;
    }
}
