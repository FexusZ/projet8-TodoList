<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Task;
    }

    protected function voteOnAttribute($attribute, $task, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($user->getRoleUser() == 'ROLE_ADMIN') {
            return true;
        }

        if (null == $task->getUser()) {
            return false;
        }
        
        switch ($attribute) {
            case 'EDIT':
                return ($task->getUser()->getId() == $user->getId());
                break;
            case 'DELETE':
                return ($task->getUser()->getId() == $user->getId());
                break;
        }

        return false;
    }
}
