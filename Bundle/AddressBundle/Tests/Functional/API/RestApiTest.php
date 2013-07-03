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
            'http://localhost/api/rest/latest/address',
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
            'http://localhost/api/rest/latest/addresses/' . $id
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
            'http://localhost/api/rest/latest/addresses/' . $id,
            $requestData
        );

        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 204);

        // open address by id
        $this->client->request(
            'GET',
            'http://localhost/api/rest/latest/addresses/' . $id
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
            'http://localhost/api/rest/latest/addresses/' . $id
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            'http://localhost/api/rest/latest/addresses/' . $id
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }

    /**
     * @return array
     */
    public function testGetCountries()
    {
        $this->client->request(
            'GET',
            "http://localhost/api/rest/latest/countries"
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        return $result;
    }

    /**
     * @depends testGetCountries
     * @param $countries
     */
    public function testGetCountry($countries)
    {
        $i = 0;
        foreach ($countries as $country) {
            $this->client->request(
                'GET',
                "http://localhost/api/rest/latest/countries/" . $country['iso2_code']
            );
            /** @var $result Response */
            $result = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($result, 200);
            $result = ToolsAPI::jsonToArray($result->getContent());
            $this->assertEquals($country, $result);
            $i++;
            if ($i % 25  == 0) {
                break;
            }
        }
    }

    /**
     * @return array
     */
    public function testGetRegions()
    {
        $this->client->request(
            'GET',
            "http://localhost/api/rest/latest/regions"
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        return $result;
    }
}
