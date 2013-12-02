<?php

namespace Oro\Bundle\UserBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

/**
 * @outputBuffering enabled
 * @db_isolation
 * @db_reindex
 */
class ControllersTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateBasicHeader());
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('oro_user_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('oro_user_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_user_user_form[enabled]'] = 1;
        $form['oro_user_user_form[username]'] = 'testUser1';
        $form['oro_user_user_form[plainPassword][first]'] = 'password';
        $form['oro_user_user_form[plainPassword][second]'] = 'password';
        $form['oro_user_user_form[firstName]'] = 'First Name';
        $form['oro_user_user_form[lastName]'] = 'Last Name';
        $form['oro_user_user_form[birthday]'] = '2013-01-01';
        $form['oro_user_user_form[email]'] = 'test@test.com';
        //$form['oro_user_user_form[tags][owner]'] = 'tags1';
        //$form['oro_user_user_form[tags][all]'] = null;
        $form['oro_user_user_form[groups][1]'] = 2;
        $form['oro_user_user_form[rolesCollection][2]'] = 4;
        //$form['oro_user_user_form[values][company][varchar]'] = 'company';
        $form['oro_user_user_form[owner]'] = 1;
        //$form['oro_user_user_form[values][gender][option]'] = 6;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("User saved", $crawler->html());
    }

    public function testUpdate()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'users-grid',
            array(
                'users-grid[_filter][username][value]' => 'testUser1'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('oro_user_update', array('id' => $result['id']))
        );

        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_user_user_form[enabled]'] = 1;
        $form['oro_user_user_form[username]'] = 'testUser1';
        $form['oro_user_user_form[firstName]'] = 'First Name Updated';
        $form['oro_user_user_form[lastName]'] = 'Last Name Updated';
        $form['oro_user_user_form[birthday]'] = '2013-01-02';
        $form['oro_user_user_form[email]'] = 'test@test.com';
        $form['oro_user_user_form[groups][1]'] = 2;
        $form['oro_user_user_form[rolesCollection][2]'] = 4;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("User saved", $crawler->html());
    }

    public function testApiGen()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'users-grid',
            array(
                'users-grid[_filter][username][value]' => 'testUser1'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->client->request(
            'GET',
            $this->client->generate('oro_user_apigen', array('id' => $result['id'])),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        /** @var User $user */
        $user = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository('OroUserBundle:User')
            ->findOneBy(array('id' => $result['id']));

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, '');

        //verify result
        $this->assertEquals($user->getApi()->getApiKey(), trim($result->getContent(), '"'));
    }

    public function testViewProfile()
    {
        $this->client->request('GET', $this->client->generate('oro_user_profile_view'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains('John Doe - Users - Users Management - System', $result->getContent());
    }

    public function testUpdateProfile()
    {
        $crawler = $this->client->request('GET', $this->client->generate('oro_user_profile_update'));
        ToolsAPI::assertJsonResponse($this->client->getResponse(), 200, 'text/html; charset=UTF-8');
        $this->assertContains(
            'John Doe - Edit - Users - Users Management - System',
            $this->client->getResponse()->getContent()
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_user_user_form[birthday]'] = '1999-01-01';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("User saved", $crawler->html());

        $crawler = $this->client->request('GET', $this->client->generate('oro_user_profile_update'));
        ToolsAPI::assertJsonResponse($this->client->getResponse(), 200, 'text/html; charset=UTF-8');
        $this->assertContains(
            'John Doe - Edit - Users - Users Management - System',
            $this->client->getResponse()->getContent()
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $this->assertEquals('1999-01-01', $form['oro_user_user_form[birthday]']->getValue());
    }
}
