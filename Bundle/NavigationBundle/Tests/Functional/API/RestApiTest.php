<?php

namespace Oro\Bundle\NavigationBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;

use Symfony\Component\HttpFoundation\Response;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestApiTest extends WebTestCase
{
    /** @var  Client */
    protected $client;

    protected static $entities;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * Data provider
     * @return array
     */
    public static function navagationItemsProvider()
    {
        return array(
            array('pinbar'),
            array('favorite'),
        );
    }

    /**
     * Test POST
     *
     * @dataProvider navagationItemsProvider
     */
    public function testPost($itemType)
    {
        self::$entities[$itemType] = array(
            'url' => 'http://url.com',
            'title' => 'Title',
            'position' => 0,
            'type' => $itemType
        );

        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_navigationitems', array('type' => $itemType)),
            self::$entities[$itemType],
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 201);

        $resultJson = json_decode($result->getContent(), true);

        $this->assertArrayHasKey('id', $resultJson);
        $this->assertGreaterThan(0, $resultJson['id']);

        self::$entities[$itemType]['id'] = $resultJson['id'];
    }

    /**
     * Test PUT
     *
     * @depends testPost
     * @dataProvider navagationItemsProvider
     */
    public function testPut($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $updatedPintab = array(
            'position' => 100
        );

        $this->client->request(
            'PUT',
            $this->client->generate(
                'oro_api_put_navigationitems_id',
                array('type' => $itemType, 'itemId' => self::$entities[$itemType]['id'])
            ),
            $updatedPintab,
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 200);

        $resultJson = json_decode($result->getContent(), true);

        $this->assertCount(0, $resultJson);
    }

    /**
     * Test GET
     *
     * @depends testPut
     * @dataProvider navagationItemsProvider
     */
    public function testGet($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_navigationitems', array('type' => $itemType)),
            array(),
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 200);
        $resultJson = json_decode($result->getContent(), true);
        $this->assertNotEmpty($resultJson);
        $this->assertArrayHasKey('id', $resultJson[0]);
    }

    /**
     * Test DELETE
     *
     * @depends testPut
     * @dataProvider navagationItemsProvider
     */
    public function testDelete($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $this->client->request(
            'DELETE',
            $this->client->generate(
                'oro_api_delete_navigationitems_id',
                array('type' => $itemType, 'itemId' => self::$entities[$itemType]['id'])
            ),
            array(),
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 204);
        $this->assertEmpty($result->getContent());
    }

    /**
     * Test 404
     *
     * @depends testDelete
     * @dataProvider navagationItemsProvider
     */
    public function testNotFound($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $this->client->request(
            'PUT',
            $this->client->generate(
                'oro_api_put_navigationitems_id',
                array('type' => $itemType, 'itemId' => self::$entities[$itemType]['id'])
            ),
            self::$entities[$itemType],
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);

        $this->client->restart();

        $this->client->request(
            'DELETE',
            $this->client->generate(
                'oro_api_delete_navigationitems_id',
                array('type' => $itemType, 'itemId' => self::$entities[$itemType]['id'])
            ),
            array(),
            array(),
            ToolsAPI::generateWsseHeader()
        );
        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }

    /**
     * Test Unauthorized
     *
     * @depends testNotFound
     * @dataProvider navagationItemsProvider
     */
    public function testUnauthorized($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $requests = array(
            'GET'    => $this->client->generate(
                'oro_api_get_navigationitems',
                array('type' => $itemType)
            ),
            'POST'   => $this->client->generate(
                'oro_api_post_navigationitems',
                array('type' => $itemType)
            ),
            'PUT'    => $this->client->generate(
                'oro_api_put_navigationitems_id',
                array('type' => $itemType, 'itemId' => self::$entities[$itemType]['id'])
            ),
            'DELETE' => $this->client->generate(
                'oro_api_delete_navigationitems_id',
                array('type' => $itemType, 'itemId' => self::$entities[$itemType]['id'])
            ),
        );

        foreach ($requests as $requestType => $url) {
            $this->client->request($requestType, $url);

            /** @var $result Response */
            $response = $this->client->getResponse();

            $this->assertEquals(401, $response->getStatusCode());

            $this->client->restart();
        }
    }

    /**
     * Test Empty Body error
     *
     * @depends testNotFound
     * @dataProvider navagationItemsProvider
     */
    public function testEmptyBody($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $requests = array(
            'POST' => $this->client->generate(
                'oro_api_post_navigationitems',
                array('type' => $itemType)
            ),
            'PUT'  => $this->client->generate(
                'oro_api_put_navigationitems_id',
                array('type' => $itemType, 'itemId' => self::$entities[$itemType]['id'])
            ),
        );

        foreach ($requests as $requestType => $url) {
            $this->client->request(
                $requestType,
                $url,
                array(),
                array(),
                ToolsAPI::generateWsseHeader()
            );

            /** @var $response Response */
            $response = $this->client->getResponse();

            ToolsAPI::assertJsonResponse($response, 400);

            $responseJson = json_decode($response->getContent(), true);

            $this->assertArrayHasKey('message', $responseJson);
            $this->assertEquals('Wrong JSON inside POST body', $responseJson['message']);

            $this->client->restart();
        }
    }
}
