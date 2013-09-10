<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Region;

class RegionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorData()
    {
        $combinedCode = 'combinedCode';

        $obj = new Region($combinedCode);
        $this->assertEquals($combinedCode, $obj->getCombinedCode());
    }

    /**
     * Test country setter
     */
    public function testCountrySetter()
    {
        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new Region('combinedCode');
        $obj->setCountry($countryMock);

        $this->assertEquals($countryMock, $obj->getCountry());
    }

    /**
     * @dataProvider provider
     * @param string $property
     */
    public function testSettersAndGetters($property)
    {
        $obj = new Region('combinedCode');
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
        $obj = new Region('combinedCode');
        $obj->setName('name');
        $this->assertEquals('name', $obj->__toString());
    }
}
