<?php

namespace Oro\Bundle\CronBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
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
        $this->client->request('GET', $this->client->generate('oro_cron_job_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testRunDaemon()
    {
        $this->client->followRedirects(true);
        $this->client->request('GET', $this->client->generate('oro_cron_job_run_daemon'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    /**
     * @depends testRunDaemon
     */
    public function testGetStatus()
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_cron_job_status'),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertGreaterThan(0, (int)$result->getContent());
    }

    /**
     * @depends testRunDaemon
     */
    public function testStopDaemon()
    {
        $this->client->request('GET', $this->client->generate('oro_cron_job_stop_daemon'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->client->request(
            'GET',
            $this->client->generate('oro_cron_job_status'),
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertEquals(0, (int)$result->getContent());
    }
}
