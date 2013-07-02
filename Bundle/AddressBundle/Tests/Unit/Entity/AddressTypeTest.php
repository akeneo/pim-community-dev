<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\AddressType;

class AddressTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     */
    public function testSettersAndGetters($property)
    {
        $name = 'testName';
        $obj = new AddressType($name);

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($name));
        $this->assertEquals($name, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));

        $this->assertEquals($name, $obj->getName());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array('label'),
        );
    }
}
