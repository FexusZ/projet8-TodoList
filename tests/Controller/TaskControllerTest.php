<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\Tests\NeedLogin;
use App\DataFixtures\TaskAttachedFixtures;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;

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

    public function testlistActionFinishedLog()
    {
        $client = static::createClient();

        $users = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
        $this->login($client, $users['user_user']);

        $crawler = $client->request('GET', '/tasks/finished');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testEditActionWithGoodCredentialsUser()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);

        $user = self::$container->get(UserRepository::class)->findOneBy(['username' => 'User1']);
        $task = self::$container->get(TaskRepository::class)->findOneBy(['user' => $user->getId()]);
        $this->login($client, $user);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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

    public function testEditActionWithGoodCredentialsAdmin()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);

        $task = self::$container->get(TaskRepository::class)->find(1);
        $user = self::$container->get(UserRepository::class)->findOneBy(['username' => 'Admin']);

        $this->login($client, $user);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

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

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(1);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => '',
            'task[content]' => ''
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('ul.list-unstyled', 'Vous devez saisir un titre.');
    }

    public function testEditActionOtherUser()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(2);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditActionUserAnonymous()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(3);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
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

    public function testToggleTaskActionWithGoodCredentials()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(1);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/toggle');

        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('div.alert.alert-success', 'La tâche ' . $task->getTitle() . ' a bien été marquée comme faite.');
    }

    public function testToggleTaskActionOtherUser()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(2);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/toggle');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }


    public function testToggleTaskActionUserAnonymous()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(3);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/toggle');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteTaskActionWithGoodCredentials()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(1);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/delete');

        $this->assertResponseRedirects('/tasks');
        $client->followRedirect();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('div.alert.alert-success', ' La tâche a bien été supprimée.');
    }

    public function testDeleteTaskActionOtherUser()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(2);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/delete');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteTaskActionUserAnonymous()
    {
        $client = static::createClient();

        $this->loadFixtures([TaskAttachedFixtures::class]);
        $task = self::$container->get(TaskRepository::class)->find(3);
        $user = self::$container->get(UserRepository::class)->find(1);

        $this->login($client, $user);

        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/delete');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
