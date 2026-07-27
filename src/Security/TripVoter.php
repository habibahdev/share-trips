<?php

namespace App\Security;

use App\Entity\Trip;
use App\Entity\User;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Trip>
 */
class TripVoter extends Voter
{
    public const EDIT = 'TRIP_EDIT';
    public const VIEW = 'TRIP_VIEW';

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW], true) && $subject instanceof Trip;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        /** @var Trip $subject */
        return $subject->getDriver()->getId() === $user->getId();
    }
}
