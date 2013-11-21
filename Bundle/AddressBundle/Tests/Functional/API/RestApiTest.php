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
    /** @var Client */
    protected $client;

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
            $this->client->generate('oro_api_post_address'),
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
            $this->client->generate('oro_api_get_address', array('id' => $id))
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
     * Test GET
     *
     * @depends testCreateAddress
     */
    public function testGetAddresses($id)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_addresses')
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 200);
        $resultJson = json_decode($result->getContent(), true);

        $this->assertNotEmpty($resultJson);
        $this->assertArrayHasKey(0, $resultJson);
        $this->assertArrayHasKey('id', $resultJson[0]);

        $this->assertEquals($id, $resultJson[0]['id']);
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
            $this->client->generate('oro_api_put_address', array('id' => $id)),
            $requestData
        );

        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 204);

        // open address by id
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_address', array('id' => $id))
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
            $this->client->generate('oro_api_delete_address', array('id' => $id))
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_address', array('id' => $id))
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
            $this->client->generate('oro_api_get_countries')
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        return array_slice($result, 0, 5);
    }

    /**
     * @depends testGetCountries
     * @param $countries
     */
    public function testGetCountry($countries)
    {
        foreach ($countries as $country) {
            $this->client->request(
                'GET',
                $this->client->generate('oro_api_get_country', array('id' => $country['iso2_code']))
            );
            /** @var $result Response */
            $result = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($result, 200);
            $result = ToolsAPI::jsonToArray($result->getContent());
            $this->assertEquals($country, $result);
        }
    }

    public function testGetRegion()
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_region'),
            array('id' => 'US.LA')
        );
        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals('US.LA', $result['combined_code']);
    }

    public function testGetCountryRegion()
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_country_get_regions', array('country' => 'US'))
        );
        /** @var $result Response */
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        foreach ($result as $region) {
            $this->client->request(
                'GET',
                $this->client->generate('oro_api_get_region'),
                array('id' => $region['combined_code'])
            );
            /** @var $result Response */
            $expectedResult = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($expectedResult, 200);
            $expectedResult = ToolsAPI::jsonToArray($expectedResult->getContent());
            $this->assertEquals($expectedResult, $region);
        }
    }
}
