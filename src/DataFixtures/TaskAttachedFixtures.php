<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TaskAttachedFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        for ($i=1; $i <= 3; $i++) {
            if ($i !== 3) {
                $user = new User();

                $user->setUsername($i < 2 ? "User$i" : 'Admin')
                    ->setEmail($i < 2 ? "User$i@email.fr" : 'Admin@email.fr')
                    ->setPassword($this->encoder->encodePassword($user, 'test'))
                    ->setRoles($i < 2 ? ['ROLE_USER'] : ['ROLE_ADMIN'])
                ;
                $manager->persist($user);
            }

            $task = (new Task())
                ->setTitle("Task$i")
                ->setContent("Content for Task$i")
            ;

            $i !== 3 ? $task->setUser($user) : "";

            $manager->persist($task);
        }
        $manager->flush();
    }
}
