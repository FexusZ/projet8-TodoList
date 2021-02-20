<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EncodePassword
{

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    // the listener methods receive an argument which gives you access to
    // both the entity object of the event and the entity manager itself
    public function prePersist(User $user, LifecycleEventArgs $event): void
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
    }

    public function preUpdate(User $user, LifecycleEventArgs $event) : void
    {
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
    }
}
