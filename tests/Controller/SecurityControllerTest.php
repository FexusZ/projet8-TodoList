<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;

/**
 * 
 */
class SecurityControllerTest extends WebTestCase
{
	use FixturesTrait;

	public function testloginAction()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/login');

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);

		$this->assertSelectorNotExists('.alert.alert-danger');
	}

	public function testloginActionWithBadCredentials()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/login');
		
		$form = $crawler->selectButton('Se connecter')->form([
			'username' => 'notgoodusername',
			'password' => 'notgoodpassword'
		]);

		$client->submit($form);

		$this->assertResponseRedirects('/login');

		$client->followRedirect();

		$this->assertSelectorExists('.alert.alert-danger');
	}

	public function testloginActionWithGoodCredentials()
	{

		$client = static::createClient();

		$this->loadFixtureFiles([ __DIR__ . '/user.yaml']);

		$crawler = $client->request('GET', '/login');

		$form = $crawler->selectButton('Se connecter')->form([
			'username' => 'test',
			'password' => 'test'
		]);

		$client->submit($form);

		$this->assertResponseRedirects('/');

		$client->followRedirect();
	}
}
