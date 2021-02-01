<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\Tests\NeedLogin;


/**
 * 
 */
class UserControllerTest extends WebTestCase
{
	use FixturesTrait;
	use NeedLogin;

	// Mettre en place un syteme de droit pour acceder a cette page.
	// public function testlistActionNotLog()
	// {
	// 	$client = static::createClient();
	// 	$crawler = $client->request('GET', '/users');
	// 	$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

	// }

	// public function testlistActionNotLogRedirect()
	// {
	// 	$client = static::createClient();
	// 	$crawler = $client->request('GET', '/users');
	// 	$this->assertResponseRedirects('/login');
	// }

	public function testlistActionLog()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/users');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
	}

	public function testEditActionWithGoodCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/user/' . $users['user_user']->getId() . '/edit');
		$form = $crawler->selectButton('Modifier')->form([
			'user[password][first]' => 'test',
			'user[password][second]' => 'test'
		]);

		$client->submit($form);

		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/users');
	}

	public function testEditActionWithBadCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/user/' . $users['user_user']->getId() . '/edit');
		$form = $crawler->selectButton('Modifier')->form([
			'user[password][first]' => '',
			'user[password][second]' => ''
		]);

		$client->submit($form);

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('ul.list-unstyled', 'Le mot de passe ne peu pas être vide.');
	}

	public function testcreateActionWithBadCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/user/create');
		$form = $crawler->selectButton('Ajouter')->form([
			'user[username]' => 'test1',
			'user[email]' => 'test',
			'user[password][first]' => 'test',
			'user[password][second]' => 'test'
		]);

		$client->submit($form);

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('ul.list-unstyled', 'Le format de l\'adresse n\'est pas correcte.');
	}

	public function testcreateActionWithGoodCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/user/create');
		$form = $crawler->selectButton('Ajouter')->form([
			'user[username]' => 'test1',
			'user[email]' => 'test1@test.fr',
			'user[password][first]' => 'test',
			'user[password][second]' => 'test'
		]);

		$client->submit($form);
		$this->assertResponseRedirects('/users');
		$client->followRedirect();
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('div.alert.alert-success', 'Superbe ! L\'utilisateur a bien été ajouté.');
	}

	public function testcreateActionWithExistingCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/user/create');
		$form = $crawler->selectButton('Ajouter')->form([
			'user[username]' => 'test',
			'user[email]' => 'test@test.fr',
			'user[password][first]' => 'test',
			'user[password][second]' => 'test'
		]);

		$client->submit($form);

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('ul.list-unstyled', 'This value is already used.');
	}
}