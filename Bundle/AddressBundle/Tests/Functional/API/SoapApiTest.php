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
            'lastName' => 'Last name',
            'namePrefix' => 'Mr.',
            'nameSuffix' => 'Sn.',
            'middleName' => 'Middle name',
            'organization' => 'Test Ltd.'
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
            'lastName' => 'Last name Updated',
            'namePrefix' => 'Mr. Updated',
            'nameSuffix' => 'Sn. Updated',
            'middleName' => 'Middle name Updated',
            'organization' => 'Test Ltd. Updated'
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
            'lastName' => 'Last name',
            'namePrefix' => 'Mr.',
            'nameSuffix' => 'Sn.',
            'middleName' => 'Middle name',
            'organization' => 'Test Ltd.'
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
            'lastName' => 'Last name Updated',
            'namePrefix' => 'Mr. Updated',
            'nameSuffix' => 'Sn. Updated',
            'middleName' => 'Middle name Updated',
            'organization' => 'Test Ltd. Updated'
        )
    );

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

    public function testCreateAddress()
    {
        $id = $this->client->getSoap()->createAddress($this->addressData['Create Address Data']);
        $this->assertInternalType('int', $id);
        $this->assertGreaterThan(0, $id);
    }

    /**
     * @depends testCreateAddress
     * @return int
     */
    public function testGetAddresses()
    {
        $result = $this->client->getSoap()->getAddresses();
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
        $result = $this->client->getSoap()->getAddress($id);
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
        $this->assertTrue($this->client->getSoap()->updateAddress($id, $this->addressData['Update Address Data']));
        $result = $this->client->getSoap()->getAddress($id);
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
        $this->assertTrue($this->client->getSoap()->deleteAddress($id));
        $this->setExpectedException('SoapFault');
        $this->client->getSoap()->getAddress($id);
    }

    /**
     * @return array
     */
    public function testGetCountries()
    {
        $result = $this->client->getSoap()->getCountries();
        $result = ToolsAPI::classToArray($result);
        return array_slice($result['item'], 0, 5);
    }

    /**
     * @depends testGetCountries
     * @param $countries
     */
    public function testGetCountry($countries)
    {
        foreach ($countries as $country) {
            $result = $this->client->getSoap()->getCountry($country['iso2Code']);
            $result = ToolsAPI::classToArray($result);
            $this->assertEquals($country, $result);
        }
    }

    /**
     * @return array
     */
    public function testGetRegions()
    {
        $result = $this->client->getSoap()->getRegions();
        $result = ToolsAPI::classToArray($result);
        return array_slice($result['item'], 0, 5);
    }

    /**
     * @depends testGetRegions
     * @param $regions
     */
    public function testGetRegion($regions)
    {
        foreach ($regions as $region) {
            $result = $this->client->getSoap()->getRegion($region['combinedCode']);
            $result = ToolsAPI::classToArray($result);
            $this->assertEquals($region, $result);
        }
    }

    /**
     * @depends testGetRegion
     */
    public function testGetCountryRegion()
    {
        $result = $this->client->getSoap()->getRegionByCountry('US');
        $result = ToolsAPI::classToArray($result);
        foreach ($result['item'] as $region) {
            $region['country'] = $region['country']['name'];
            $expectedResult = $this->client->getSoap()->getRegion($region['combinedCode']);
            $expectedResult = ToolsAPI::classToArray($expectedResult);
            $this->assertEquals($expectedResult, $region);
        }
    }
}
