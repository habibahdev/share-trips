<?php

namespace App\Security;

use App\Entity\Conversation;
use App\Entity\User;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Conversation>
 */
class ConversationVoter extends Voter
{
    #[Override]
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'CONVERSATION_VIEW' && $subject instanceof Conversation;
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        assert($user instanceof User);

        /** @var Conversation $subject */
        return $user === $subject->getDriver() || $user === $subject->getPassenger();
    }
}
