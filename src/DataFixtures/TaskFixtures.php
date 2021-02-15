<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Task;

class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
    	for ($i=1; $i <= 10; $i++) {
    		$task = (new Task())
    			->setTitle("Task$i")
    			->setContent("Content for Task$i")
    		;
        	$manager->persist($task);
    	}
        $manager->flush();
    }
}
