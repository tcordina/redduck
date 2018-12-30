<?php

namespace App\Security\Voter;

use App\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class MessageVoter extends Voter
{
    private $security;
    private $user;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['MESSAGE_EDIT', 'MESSAGE_DELETE'])
            && $subject instanceof Message;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $this->user = $token->getUser();
        if (!$this->user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'MESSAGE_EDIT':
                return $this->allowEdit($subject);
                break;
            case 'MESSAGE_DELETE':
                return $this->allowEdit($subject);
                break;
        }
        return false;
    }

    private function allowEdit(Message $message)
    {
        if ($message->getAuthor() === $this->user ||
            $this->security->isGranted('ROLE_ADMIN') ||
            $this->security->isGranted('ROLE_MODERATOR')
        ) {
            return true;
        }
        return false;
    }

}
