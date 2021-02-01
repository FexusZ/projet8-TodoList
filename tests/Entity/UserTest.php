<?php

namespace App\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\User;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\DataFixtures\UserFixtures;

/**
 * 
 */
class UserTest extends KernelTestCase
{
	use FixturesTrait;

	public function getEntity()
	{
		return (new User)->setEmail('test@email.fr')
			->setUsername('test')
			->setPassword('test')
		;
	}

	public function getAssert(User $user, int $error_number)
	{
		self::bootKernel();
		$this->loadFixtures([UserFixtures::class]);
		$error = self::$container->get('validator')->validate($user);
		$this->assertCount($error_number, $error);
	}

	public function testValidEntity()
	{
		$this->getAssert($this->getEntity(), 0);
	}

	public function testInvalidEntity()
	{
		$this->getAssert($this->getEntity()->setEmail('test'), 1);
	}

	public function testInvalidBlankEntityUsername()
	{
		$this->getAssert($this->getEntity()->setUsername(''), 1);
	}

	public function testInvalidBlankEntityEmail()
	{
		$this->getAssert($this->getEntity()->setEmail(''), 1);
	}

	public function testInvalidBlankEntityPassword()
	{
		$this->getAssert($this->getEntity()->setPassword(''), 1);
	}

	public function testInvalidUsedEntity()
	{
		$this->getAssert($this->getEntity()->setEmail('User1@email.fr'), 1);
	}
}