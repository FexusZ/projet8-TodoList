<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

	public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
    	for ($i=1; $i <= 10; $i++) {
    		$user = new User();

    		$user->setUsername("User$i")
    			->setEmail("User$i@email.fr")
    			->setPassword($this->encoder->encodePassword($user, 'test'))
                ->setRoleUser($i < 10 ? 'ROLE_USER' : 'ROLE_ADMIN')
    		;
        	$manager->persist($user);
    	}
        $manager->flush();
    }
}
