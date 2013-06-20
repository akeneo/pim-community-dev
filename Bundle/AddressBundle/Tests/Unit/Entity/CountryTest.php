<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

class CountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     */
    public function testSettersAndGetters($property)
    {
        $obj = new Country('iso2code');
        $value = 'testValue';

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function testConstructorData()
    {
        $obj = new Country('iso2Code');

        $this->assertEquals('iso2Code', $obj->getIso2Code());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array('name'),
            array('iso3code'),
            array('regions'),
            array('locale'),
        );
    }

    public function testToString()
    {
        $obj = new Country('iso2Code');
        $obj->setName('name');
        $this->assertEquals('name', $obj->__toString());
    }

    public function testAddRegion()
    {
        $country = new Country('iso2Code');
        $region = new Region('combinedCode');

        $this->assertEmpty($country->getRegions()->getValues());

        $country->addRegion($region);

        $this->assertEquals(array($region), $country->getRegions()->getValues());
        $this->assertEquals($country, $region->getCountry());
    }

    public function testRemoveRegion()
    {
        $country = new Country('iso2Code');
        $region = new Region('combinedCode');
        $country->addRegion($region);

        $this->assertNotEmpty($country->getRegions()->getValues());

        $country->removeRegion($region);

        $this->assertEmpty($country->getRegions()->getValues());
        $this->assertNull($region->getCountry());
    }

    public function testHasRegions()
    {
        $country = new Country('iso2Code');
        $region = new Region('combinedCode');

        $this->assertFalse($country->hasRegions());

        $country->addRegion($region);

        $this->assertTrue($country->hasRegions());
    }
}
