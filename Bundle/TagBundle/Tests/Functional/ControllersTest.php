<?php

namespace Oro\Bundle\TagBundle\Tests\Functional;

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
        $this->client->request('GET', $this->client->generate('oro_tag_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testIndexJson()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'tag-grid',
            array(
                'tag-grid[_pager][_page]' => 1,
                'tag-grid[_pager][_per_page]' => 10,
                'tag-grid[_sort_by][name]' => 'DESC'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals(0, $result['options']['totalRecords']);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('oro_tag_create'));
        $form = $crawler->selectButton('Save')->form();
        $form['oro_tag_tag_form[name]'] = 'tag758';
        $form['oro_tag_tag_form[owner]'] = 1;
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Tag saved", $crawler->html());
    }

    public function testUpdate()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'tag-grid',
            array(
                'tag-grid[_filter][name][value]' => 'tag758'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request('GET', $this->client->generate('oro_tag_update', array('id' => $result['id'])));
        $form = $crawler->selectButton('Save')->form();
        $form['oro_tag_tag_form[name]'] = 'tag758_updated';
        $form['oro_tag_tag_form[owner]'] = 1;
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Tag saved", $crawler->html());

        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'tag-grid',
            array(
                'tag-grid[_filter][name][value]' => 'tag758_updated'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        $this->assertEquals('tag758_updated', $result['name']);
    }

    public function testSearch()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'tag-grid',
            array(
                'tag-grid[_filter][name][value]' => 'tag758_updated'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->client->request('GET', $this->client->generate('oro_tag_search', array('id' => $result['id'])));
        $result = $this->client->getResponse();

        $this->assertContains('Records tagged as "tag758_updated"', $result->getContent());
        $this->assertContains('No results were found', $result->getContent());
    }
}
