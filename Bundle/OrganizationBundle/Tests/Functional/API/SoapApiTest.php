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
class SoapApiTest extends WebTestCase
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
                'parent' => null,
                'appendUsers' => array(1),
                'owner' => '1',
            )
    );

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
     * Test POST
     * @return string
     */
    public function testCreate()
    {
        $this->markTestSkipped('BAP-1375');
        $result = $this->client->getSoap()->createBusinessUnit($this->fixtureData['business_unit']);
        $responseData = ToolsAPI::classToArray($result);
        $this->assertNotEmpty($responseData);
        $this->assertInternalType('array', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        return $responseData['id'];
    }
}
