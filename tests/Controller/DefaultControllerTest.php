<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\Tests\NeedLogin;


/**
 * 
 */
class DefaultControllerTest extends WebTestCase
{
	use FixturesTrait;
	use NeedLogin;

	public function testIndexActionNotLog()
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
	}

	public function testIndexActionNotLogRedirect()
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/');
		$this->assertResponseRedirects('/login');
	}

	public function testIndexActionLog()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
	}
}
