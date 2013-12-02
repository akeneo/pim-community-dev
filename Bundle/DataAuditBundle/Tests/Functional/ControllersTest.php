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

    /**
     * @return array
     */
    public function testAudit()
    {
        $this->prepareFixture();

        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'audit-grid',
            array(
                'audit-grid[_filter][objectName][type]' => 1,
                'audit-grid[_filter][objectName][value]' => $this->userData['username'],
                'audit-grid[_filter][objectClass][value]' => 'Oro\\Bundle\\CalendarBundle\\Entity\\User'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        return $result;
    }

    /**
     * @depends testAudit
     * @param $result
     */
    public function testAuditHistory($result)
    {

        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'audit-history-grid',
            array(
                'audit-history-grid[object_class]' => str_replace('\\', '_', $result['objectClass']),
                'audit-history-grid[object_id]' => $result['objectId']
            )
        );

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
        $dateObject = new \DateTime($date);
        $dateObject->setTimezone(new \DateTimeZone('UTC'));
        return $dateObject->format('Y-m-d');
    }
}
