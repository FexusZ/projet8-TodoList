<?php

namespace App\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Entity\Task;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\DataFixtures\TaskFixtures;

/**
 *
 */
class TaskTest extends KernelTestCase
{
    use FixturesTrait;

    public function getEntity()
    {
        return (new Task)->setTitle('Task')
            ->setContent('Content of Task')
        ;
    }

    public function getAssert(Task $task, int $error_number)
    {
        self::bootKernel();
        $this->loadFixtures([TaskFixtures::class]);

        $error = self::$container->get('validator')->validate($task);
        $this->assertCount($error_number, $error);
    }

    public function testValidEntity()
    {
        $this->getAssert($this->getEntity(), 0);
    }

    public function testInvalidBlankEntityTitle()
    {
        $this->getAssert($this->getEntity()->setTitle(''), 1);
    }

    public function testInvalidBlankEntityContent()
    {
        $this->getAssert($this->getEntity()->setContent(''), 1);
    }

    public function testInvalidBlankEntityCreatedAt()
    {
        $this->getAssert($this->getEntity()->setCreatedAt(''), 1);
    }
}
