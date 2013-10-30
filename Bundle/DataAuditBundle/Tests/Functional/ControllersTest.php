<?php

namespace Oro\Bundle\DataAudit\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class ControllersTest extends WebTestCase
{
    protected $userData = array(
        'username' => 'testAdmin',
        'email' => 'test@test.com',
        'namePrefix' => 'Mr.',
        'firstName' => 'FirstNameAudit',
        'middleName' => 'MiddleName',
        'lastName' => 'LastNameAudit',
        'nameSuffix' => 'Sn.',
        'birthday' => '2013-01-01',
        'enabled' => 1,
        'roles' => 'Administrator',
        'groups' => 'Sales',
        'company' => 'company',
        'gender' => 'Male'
    );
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->userData['birthday'] = $this->getFormattedDate($this->userData['birthday']);
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function prepareFixture()
    {
        $crawler = $this->client->request('GET', $this->client->generate('oro_user_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_user_user_form[enabled]'] = $this->userData['enabled'];
        $form['oro_user_user_form[username]'] = $this->userData['username'];
        $form['oro_user_user_form[plainPassword][first]'] = 'password';
        $form['oro_user_user_form[plainPassword][second]'] = 'password';
        $form['oro_user_user_form[firstName]'] = $this->userData['firstName'];
        $form['oro_user_user_form[lastName]'] = $this->userData['lastName'];
        $form['oro_user_user_form[birthday]'] = $this->userData['birthday'];
        $form['oro_user_user_form[email]'] = $this->userData['email'];
        $form['oro_user_user_form[groups][1]'] = 2;
        $form['oro_user_user_form[rolesCollection][1]'] = true;
        $form['oro_user_user_form[values][company][varchar]'] = $this->userData['company'];
        $form['oro_user_user_form[owner]'] = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("User saved", $crawler->html());
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('oro_dataaudit_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testHistory()
    {
        $this->prepareFixture();
        $this->client->request(
            'GET',
            $this->client->generate('oro_dataaudit_index', array('_format' =>'json')),
            array(
                'audit[_filter][objectName][type]' => null,
                'audit[_filter][objectName][value]' => $this->userData['username'],
                'audit[_pager][_page]' => 1,
                'audit[_pager][_per_page]' => 10,
                'audit[_sort_by][action]' => 'ASC')
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        $this->client->request(
            'GET',
            $this->client->generate(
                'oro_dataaudit_history',
                array(
                    'entity' => str_replace('\\', '_', $result['objectClass']),
                    'id' => $result['objectId'],
                    '_format' =>'json'
                )
            )
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $result['old'] = $this->clearResult($result['old']);
        $result['new'] = $this->clearResult($result['new']);

        foreach ($result['old'] as $auditRecord) {
            $auditValue = explode(': ', $auditRecord, 2);
            $this->assertEmpty(trim($auditValue[1]));
        }

        foreach ($result['new'] as $auditRecord) {
            $auditValue = explode(': ', $auditRecord, 2);
            $key = trim($auditValue[0]);
            $value = trim($auditValue[1]);
            if ($key == 'birthday') {
                $value = $this->getFormattedDate($value);
            }
            $this->assertEquals($this->userData[$key], $value);
        }

        $this->assertEquals('John Doe  - admin@example.com', $result['author']);

    }

    protected function clearResult($result)
    {
        $result = preg_replace("/\n+ */", "\n", $result);
        $result = strip_tags($result);
        $result = explode("\n", trim($result, "\n"));

        return array_filter($result);
    }

    /**
     * Get formatted date acceptable by oro_date type.
     *
     * @param string $date
     * @return bool|string
     */
    protected function getFormattedDate($date)
    {
        $date = new \DateTime($date);
        $formatter = new \IntlDateFormatter(
            \Locale::getDefault(),
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $date->getTimezone()->getName(),
            \IntlDateFormatter::GREGORIAN
        );
        return $formatter->format($date);
    }
}
