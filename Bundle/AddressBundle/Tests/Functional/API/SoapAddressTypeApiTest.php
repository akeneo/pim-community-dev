<?php

namespace Oro\Bundle\AddressBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 */
class SoapAddressTypeApiTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        $this->client->soap(
            "http://localhost/api/soap",
            array(
                'location' => 'http://localhost/api/soap',
                'soap_version' => SOAP_1_2
            )
        );
    }

    /**
     * @return array
     */
    public function testGetAddressTypes()
    {
        $result = $this->client->getSoap()->getAddressTypes();
        $result = ToolsAPI::classToArray($result);
        if (is_array(reset($result['item']))) {
            $actualData = $result['item'];
        } else {
            $actualData[] = $result['item'];
        }
        $this->assertNotEmpty($actualData);

        return $actualData;
    }

    /**
     * @depends testGetAddressTypes
     * @param array $expected
     */
    public function testGetAddressType($expected)
    {
        foreach ($expected as $addrType) {
            $result = $this->client->getSoap()->getAddressType($addrType['name']);
            $result = ToolsAPI::classToArray($result);
            $this->assertNotEmpty($result);
            $this->assertEquals($addrType, $result);
        }
    }
}
