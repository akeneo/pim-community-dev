<?php

namespace Oro\Bundle\AddressBundle\Tests\Functional\API;

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
    /** @var Client  */
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * Test POST
     *
     */
    public function testCreateAddress()
    {
        $requestData = array('address' =>
            array(
                'street'      => 'Some kind st.',
                'city'        => 'Old York',
                'state'       => 'US.AL',
                'country'     => 'US',
                'postalCode'  => '32422',
            )
        );

        $this->client->request(
            'POST',
            'address',
            $requestData
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 201);

        $responseData = $result->getContent();
        $this->assertNotEmpty($responseData);
        $responseData = json_decode($responseData, true);
        $this->assertInternalType('array', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        return $responseData['id'];
    }

    /**
     * Test GET
     *
     * @depends testCreateAddress
     */
    public function testGetAddress($id)
    {
        $this->client->request(
            'GET',
            'addresses/' . $id
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 200);
        $resultJson = json_decode($result->getContent(), true);

        $this->assertNotEmpty($resultJson);
        $this->assertArrayHasKey('id', $resultJson);

        $this->assertEquals($id, $resultJson['id']);
    }

    /**
     * Test PUT
     *
     * @depends testCreateAddress
     */
    public function testUpdateAddress($id)
    {
        // update
        $requestData = array('address' =>
            array(
                'street'      => 'Updated street',
                'street2'      => 'street2 UP'
            )
        );

        $this->client->request(
            'PUT',
            'addresses/' . $id,
            $requestData
        );

        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 204);

        // open address by id
        $this->client->request(
            'GET',
            'addresses/' . $id
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = json_decode($result->getContent(), true);

        // compare result
        foreach ($requestData['address'] as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * Test DELETE
     *
     * @depends testCreateAddress
     */
    public function testDeleteAddress($id)
    {
        $this->client->request(
            'DELETE',
            'addresses/' . $id
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            'addresses/' . $id
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
