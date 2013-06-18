<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Region;

class RegionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorData()
    {
        $obj = new Region();

        $this->assertNull($obj->getId());
        $obj->setLocale();
    }

    /**
     * Test country setter
     */
    public function testCountrySetter()
    {
        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')->disableOriginalConstructor()->getMock();

        $obj = new Region();
        $obj->setCountry($countryMock);

        $this->assertEquals($countryMock, $obj->getCountry());
        $this->assertNull($obj->getId());
    }

    /**
     * @dataProvider provider
     * @param string $property
     */
    public function testSettersAndGetters($property)
    {
        $obj = new Region();
        $value = 'testValue';

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
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
            array('code'),
            array('locale'),
        );
    }

    public function testToString()
    {
        $obj = new Region();
        $obj->setName('TEST');
        $this->assertEquals('TEST', (string)$obj);
    }
}
