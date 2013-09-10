<?php
namespace Oro\Bundle\AddressBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\BrowserKit\Response;

/**
 * @outputBuffering enabled
 */
class RestAddressTypeApiTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * @return array
     */
    public function testGetAddressTypes()
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_addresstypes')
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());

        $this->assertNotEmpty($result);
        return $result;
    }

    /**
     * @depends testGetAddressTypes
     * @param array $expected
     */
    public function testGetAddressType($expected)
    {
        foreach ($expected as $addrType) {
            $this->client->request(
                'GET',
                $this->client->generate('oro_api_get_addresstype', array('name' => $addrType['name']))
            );
            /** @var $result Response */
            $result = $this->client->getResponse();

            ToolsAPI::assertJsonResponse($result, 200);
            $result = ToolsAPI::jsonToArray($result->getContent());
            $this->assertNotEmpty($result);
            $this->assertEquals($addrType, $result);
        }
    }
}
