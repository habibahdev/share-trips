<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\MessageRepository;
use Override;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtensions extends AbstractExtension
{
    public function __construct(
        private MessageRepository $messageRepository,
        private Security $security
    ) {
    }

    #[Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('unread_messages_count', $this->getUnreadCount(...))
        ];
    }

    public function getUnreadCount(): int
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        return $this->messageRepository->countUnreadForUser($user);
    }
}
