<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use App\Tests\NeedLogin;

/**
 *
 */
class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;
    use NeedLogin;

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

    public function testloginActionWithNoToken()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        
        $form = $crawler->selectButton('Se connecter')->form([
            'username' => 'notgoodusername',
            'password' => 'notgoodpassword',
            '_csrf_token' => ''
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/login');

        $client->followRedirect();

        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testloginActionWithGoodCredentials()
    {
        $client = static::createClient();

        $user = $this->loadFixtureFiles([ __DIR__ . '/user.yaml']);
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            'username' => 'test',
            'password' => 'test'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/');

    }

    public function testlogoutCheck()
    {
        $client = static::createClient();

        $users = $this->loadFixtureFiles([
            __DIR__ . '/user.yaml',
        ]);

        $this->login($client, $users['user_user']);
        $crawler = $client->request('GET', '/logout');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }
}
