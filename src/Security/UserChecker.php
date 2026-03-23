<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
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
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Vous devez vérifier votre adresse e-mail.'
            );
        }
        if ($user->isBanned()) {
            throw new CustomUserMessageAuthenticationException(
                'Vous avez été banni de la plateforme.'
            );
        }
        if ($user->isSuspended()) {
            throw new CustomUserMessageAuthenticationException(
                sprintf(
                    'Votre compte est suspendu jusqu\'au %s. Vous pouvez contacter la plateforme',
                    $user->getSuspendedUntil()->format('d/m/Y à H:i')
                )
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
    }
}
