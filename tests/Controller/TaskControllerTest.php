<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\Tests\NeedLogin;


/**
 * 
 */
class TaskControllerTest extends WebTestCase
{
	use FixturesTrait;
	use NeedLogin;

	// Mettre en place un syteme de droit pour acceder a cette page.
	public function testlistActionNotLog()
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/tasks');
		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
	}

	public function testlistActionNotLogRedirect()
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/tasks');
		$this->assertResponseRedirects('/login');
	}

	public function testlistActionLog()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/tasks');
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
	}

	public function testEditActionWithGoodCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([
		 	__DIR__ . '/user.yaml',
		 	__DIR__ . '/task.yaml'
		]);

		$this->login($client, $users['user_user']);
		$crawler = $client->request('GET', '/tasks/' . $users['task']->getId() . '/edit');

		$form = $crawler->selectButton('Modifier')->form([
			'task[title]' => 'test task',
			'task[content]' => 'content of test task'
		]);
		$client->submit($form);

		$this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
		$this->assertResponseRedirects('/tasks');
		$client->followRedirect();
		$this->assertSelectorTextContains('div.alert.alert-success', 'Superbe ! La tâche a bien été modifiée.');
	}

	public function testEditActionWithBadCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([
		 	__DIR__ . '/user.yaml',
		 	__DIR__ . '/task.yaml'
		]);

		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/tasks/' . $users['task']->getId() . '/edit');
		$form = $crawler->selectButton('Modifier')->form([
			'task[title]' => '',
			'task[content]' => ''
		]);

		$client->submit($form);

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('ul.list-unstyled', 'Vous devez saisir un titre.');
	}

	public function testcreateActionWithBadCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([
		 	__DIR__ . '/user.yaml',
		 	__DIR__ . '/task.yaml'
		]);

		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/tasks/create');
		$form = $crawler->selectButton('Ajouter')->form([
			'task[title]' => '',
			'task[content]' => ''
		]);

		$client->submit($form);

		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('ul.list-unstyled', 'Vous devez saisir un titre.');
	}

	public function testcreateActionWithGoodCredentials()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([
		 	__DIR__ . '/user.yaml',
		 	__DIR__ . '/task.yaml'
		]);

		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/tasks/create');
		$form = $crawler->selectButton('Ajouter')->form([
			'task[title]' => 'test',
			'task[content]' => 'test'
		]);

		$client->submit($form);

		$this->assertResponseRedirects('/tasks');
		$client->followRedirect();
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('div.alert.alert-success', 'Superbe ! La tâche a été bien été ajoutée.');
	}

	public function testToggleTaskAction()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([
		 	__DIR__ . '/user.yaml',
		 	__DIR__ . '/task.yaml'
		]);

		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/tasks/' . $users['task']->getId() . '/toggle');

		$this->assertResponseRedirects('/tasks');
		$client->followRedirect();
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('div.alert.alert-success', ' La tâche task a bien été marquée comme faite.');
	}

	public function testDeleteTaskAction()
	{
		$client = static::createClient();

		$users = $this->loadFixtureFiles([
		 	__DIR__ . '/user.yaml',
		 	__DIR__ . '/task.yaml'
		]);

		$this->login($client, $users['user_user']);

		$crawler = $client->request('GET', '/tasks/' . $users['task']->getId() . '/delete');

		$this->assertResponseRedirects('/tasks');
		$client->followRedirect();
		$this->assertResponseStatusCodeSame(Response::HTTP_OK);
		$this->assertSelectorTextContains('div.alert.alert-success', ' La tâche a bien été supprimée.');
	}
}