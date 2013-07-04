<?php

namespace Oro\Bundle\AddressBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapApiTest extends WebTestCase
{
    /** @var array Address Test Data */
    protected $addressData = array(
        'Create Address Data' => array(
            'label' => 'created address',
            'street' => 'Some kind st.',
            'street2' => 'Second st.',
            'city' => 'Old York',
            'state' => 'US.AL',
            'country' => 'US',
            'postalCode' => '32422',
            'firstName' => 'First name',
            'lastName' => 'Last name'
        ),
        'Update Address Data' => array(
            'label' => 'updated address',
            'street' => 'Some kind st. Updated',
            'street2' => 'Second st. Updated',
            'city' => 'Los Angeles',
            'stateText' => null,
            'state' => 'US.CA',
            'country' => 'US',
            'postalCode' => '32422',
            'firstName' => 'First name Updated',
            'lastName' => 'Last name Updated'
        ),
        'Expected Address Data' => array(
            'label' => 'created address',
            'street' => 'Some kind st.',
            'street2' => 'Second st.',
            'city' => 'Old York',
            'stateText' => null,
            'state' => 'Alabama',
            'country' => 'United States',
            'postalCode' => '32422',
            'firstName' => 'First name',
            'lastName' => 'Last name'
        ),
        'Expected Updated Address Data' => array(
            'label' => 'updated address',
            'street' => 'Some kind st. Updated',
            'street2' => 'Second st. Updated',
            'city' => 'Los Angeles',
            'stateText' => null,
            'state' => 'California',
            'country' => 'United States',
            'postalCode' => '32422',
            'firstName' => 'First name Updated',
            'lastName' => 'Last name Updated'
        )
    );

    /** @var Client */
    protected $client = null;

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

    public function testCreateAddress()
    {
        $this->assertTrue($this->client->soapClient->createAddress($this->addressData['Create Address Data']));
    }

    /**
     * @depends testCreateAddress
     * @return int
     */
    public function testGetAddresses()
    {
        $result = $this->client->soapClient->getAddresses();
        $result = ToolsAPI::classToArray($result);
        if (is_array(reset($result['item']))) {
            $actualData = $result['item'];
        } else {
            $actualData[] = $result['item'];
        }

        $expectedData = $this->addressData['Expected Address Data'];
        //search expected result
        $actualData = array_filter(
            $actualData,
            function ($a) use ($expectedData) {
                return count(array_intersect($expectedData, $a)) == count($expectedData);
            }
        );
        $this->assertCount(1, $actualData);
        //remember Id for next test
        $id = reset($actualData);
        $id = $id['id'];
        //remove Id field
        $actualData = array_map(
            function ($a) {
                unset($a['id']);
                return $a;
            },
            $actualData
        );
        $this->assertEquals($this->addressData['Expected Address Data'], reset($actualData));
        return $id;
    }

    /**
     * @depends testGetAddresses
     * @param $id
     */
    public function testGetAddress($id)
    {
        $result = $this->client->soapClient->getAddress($id);
        $actualData = ToolsAPI::classToArray($result);
        unset($actualData['id']);
        $this->assertEquals($this->addressData['Expected Address Data'], $actualData);
    }

    /**
     * @depends testGetAddresses
     * @param $id
     */
    public function testUpdateAddress($id)
    {
        $this->assertTrue($this->client->soapClient->updateAddress($id, $this->addressData['Update Address Data']));
        $result = $this->client->soapClient->getAddress($id);
        $actualData = ToolsAPI::classToArray($result);
        unset($actualData['id']);
        $this->assertEquals($this->addressData['Expected Updated Address Data'], $actualData);
    }

    /**
     * @depends testGetAddresses
     * @param $id
     */
    public function testDeleteAddress($id)
    {
        $this->assertTrue($this->client->soapClient->deleteAddress($id));
        $this->setExpectedException('SoapFault');
        $this->client->soapClient->getAddress($id);
    }

    /**
     * @return array
     */
    public function testGetCountries()
    {
        $this->markTestSkipped('BAP-1072');
        $result = $this->client->soapClient->getCountries();
        $result = ToolsAPI::classToArray($result);
        return $result['item'];
    }

    /**
     * @depends testGetCountries
     * @param $countries
     */
    public function testGetCountry($countries)
    {
        $i = 0;
        foreach ($countries as $country) {
            $result = $this->client->soapClient->getCountry($country['iso2_code']);
            $result = ToolsAPI::classToArray($result);
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
        $this->markTestSkipped('BAP-1072');
        $result = $this->client->soapClient->getRegions();
        $result = ToolsAPI::classToArray($result);
        return $result['item'];
    }

    /**
     * @depends testGetRegions
     * @param $regions
     */
    public function testGetRegion($regions)
    {
        $i = 0;
        foreach ($regions as $region) {
            $i = 0;
            $result = $this->client->soapClient->getRegion($region['combined_code']);
            $result = ToolsAPI::classToArray($result);
            $this->assertEquals($region, $result['item']);
            $i++;
            if ($i % 25  == 0) {
                break;
            }
        }
    }

    /**
     * @depends testGetRegion
     */
    public function testGetCountryRegion()
    {
        $result = $this->client->soapClient->getCountryRegions('US');
        $result = ToolsAPI::classToArray($result);
        foreach ($result['item'] as $region) {
            $expectedResult = $this->client->soapClient->getRegion($region['combined_code']);
            $expectedResult = ToolsAPI::classToArray($expectedResult);
            $this->assertEquals($expectedResult['item'], $region);
        }
    }
}
