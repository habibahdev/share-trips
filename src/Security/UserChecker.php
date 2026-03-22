<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
        if ($user->isBanned()) {
            throw new CustomUserMessageAuthenticationException(
                'Votre compte a été banni.'
            );
        }
        if ($user->isSuspended()) {
            throw new CustomUserMessageAuthenticationException(
                sprintf(
                    'Vous avez été suspendu jusqu\'au %s.',
                    $user->getSuspendedUntil()->format('d/m/Y à H:i') . ' ' .
                    '. Vous pouvez contacter la plateforme'
                )
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
