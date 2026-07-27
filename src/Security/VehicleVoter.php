<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Vehicle;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Vehicle>
 */
class VehicleVoter extends Voter
{
    public const EDIT = 'VEHICLE_EDIT';

    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof Vehicle;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        /** @var Vehicle $subject */
        return $subject->getUsser()?->getId() === $user->getId();
    }
}
