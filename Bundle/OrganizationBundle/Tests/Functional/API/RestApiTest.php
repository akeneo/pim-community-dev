<?php

namespace Oro\Bundle\OrganizationBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\BrowserKit\Response;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestApiTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    protected $fixtureData = array('business_unit' =>
            array(
                'name'          => 'BU Name',
                'organization'  => '1',
                'phone' => '123-123-123',
                'website' => 'http://localhost',
                'email' => 'email@email.localhost',
                'fax' => '321-321-321',
                'appendUsers' => array(1),
                'owner' => '1',
            )
    );

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * Test POST
     * @return string
     */
    public function testCreate()
    {

        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_businessunit'),
            $this->fixtureData
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 201);

        $responseData = $result->getContent();
        $this->assertNotEmpty($responseData);
        $responseData = ToolsAPI::jsonToArray($responseData);
        $this->assertInternalType('array', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        return $responseData['id'];
    }

    /**
     * Test GET
     *
     * @depends testCreate
     * @param string $id
     */
    public function testGets($id)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_businessunits')
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 200);
        $responseData = $result->getContent();
        $this->assertNotEmpty($responseData);
        $responseData = ToolsAPI::jsonToArray($responseData);
        $initialCount = $this->getCount();
        foreach ($responseData as $row) {
            if ($row['id'] == $id) {
                $this->assertEquals($this->fixtureData['business_unit']['name'], $row['name']);
                $this->assertEquals($this->fixtureData['business_unit']['phone'], $row['phone']);
                $this->assertEquals($this->fixtureData['business_unit']['fax'], $row['fax']);
                $this->assertEquals($this->fixtureData['business_unit']['email'], $row['email']);
                $this->assertEquals($this->fixtureData['business_unit']['website'], $row['website']);
                $this->assertEquals('default', $row['organization']);
                $this->assertEmpty($row['users']);
            }
        }

        $this->assertGreaterThan($initialCount, $this->getCount(), 'Created Business Unit is not in list');
    }

    /**
     * Test GET
     *
     * @depends testCreate
     * @param string $id
     */
    public function testGet($id)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_businessunit', array('id' => $id))
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 200);
        $responseData = ToolsAPI::jsonToArray($result->getContent());

        $this->assertNotEmpty($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($id, $responseData['id']);
        $this->assertEquals($this->fixtureData['business_unit']['name'], $responseData['name']);
        $this->assertEquals($this->fixtureData['business_unit']['phone'], $responseData['phone']);
        $this->assertEquals($this->fixtureData['business_unit']['fax'], $responseData['fax']);
        $this->assertEquals($this->fixtureData['business_unit']['email'], $responseData['email']);
        $this->assertEquals($this->fixtureData['business_unit']['website'], $responseData['website']);
        $this->assertEquals('default', $responseData['organization']);
        $this->assertEmpty($responseData['users']);
    }

    /**
     * Test PUT
     *
     * @depends testCreate
     * @param string $id
     */
    public function testUpdate($id)
    {
        $requestData = $this->fixtureData;
        $requestData['business_unit']['name'] = $requestData['business_unit']['name'] . '_updated';
        $this->client->request(
            'PUT',
            $this->client->generate('oro_api_put_businessunit', array('id' => $id)),
            $requestData
        );

        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 204);

        // open address by id
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_businessunit', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals($requestData['business_unit']['name'], $result['name']);

    }

    /**
     * Test DELETE
     *
     * @depends testCreate
     * @param string $id
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_businessunit', array('id' => $id))
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_businessunit', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
