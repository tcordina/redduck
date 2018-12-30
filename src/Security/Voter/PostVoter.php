<?php

namespace App\Security\Voter;

use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter extends Voter
{
    private $security;
    private $user;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['POST_EDIT', 'POST_DELETE'])
            && $subject instanceof Post;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $this->user = $token->getUser();
        if (!$this->user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'POST_EDIT':
                return $this->allowEdit($subject);
                break;
            case 'POST_DELETE':
                return $this->allowEdit($subject);
                break;
        }
        return false;
    }

    private function allowEdit(Post $post)
    {
        if ($post->getAuthor() === $this->user ||
            $this->security->isGranted('ROLE_ADMIN') ||
            $this->security->isGranted('ROLE_MODERATOR')
        ) {
            return true;
        }
        return false;
    }

}
