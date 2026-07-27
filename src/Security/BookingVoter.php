<?php

namespace App\Security;

use App\Entity\Booking;
use App\Entity\User;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Booking>
 */
class BookingVoter extends Voter
{
    public const VIEW = 'BOOKING_VIEW';
    public const CANCEL = 'BOOKING_CANCEL';
    public const CONFIRM = 'BOOKING_CONFIRM';

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::CANCEL, self::CONFIRM], true) && $subject instanceof Booking;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        /** @var Booking $subject */
        return match ($attribute) {
            self::VIEW, self::CANCEL => $subject->getPassenger()->getId() === $user->getId(),
            self::CONFIRM => $subject->getTrip()->getDriver()->getId() === $user->getId(),
            default => false
        };
    }
}
