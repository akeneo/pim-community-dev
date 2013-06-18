<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Country;

class CountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     */
    public function testSettersAndGetters($property)
    {
        $obj = new Country();
        $value = 'testValue';

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function testConstructorData()
    {
        $obj = new Country('name', 'iso2Code', 'iso3Code');

        $this->assertEquals('name', $obj->getName());
        $this->assertEquals('iso2Code', $obj->getIso2Code());
        $this->assertEquals('iso3Code', $obj->getIso3Code());
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
            array('iso2code'),
            array('iso3code'),
            array('regions'),
        );
    }

    /**
     * @dataProvider regionsDataProvider
     * @param array $regions
     * @param bool $expected
     */
    public function testHasRegions($regions, $expected)
    {
        $obj = new Country('name', 'iso2Code', 'iso3Code');
        $obj->setRegions($regions);
        $this->assertEquals($expected, $obj->hasRegions());
    }

    public function regionsDataProvider()
    {
        return array(
            array(null, false),
            array(array('AL'), true)
        );
    }

    public function testToString()
    {
        $obj = new Country('name', 'iso2Code', 'iso3Code');
        $this->assertEquals('name', $obj->__toString());
    }
}
