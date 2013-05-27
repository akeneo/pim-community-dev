<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new Address();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function testBeforeSave()
    {
        $obj = new Address();
        $obj->beforeSave();

        $this->assertNotNull($obj->getCreatedAt());
        $this->assertNotNull($obj->getUpdatedAt());

        $this->assertEquals($obj->getCreatedAt(), $obj->getUpdatedAt());
    }

    public function testToString()
    {
        $obj = new Address();
        $country = $this->getMock('Oro\Bundle\AddressBundle\Entity\Country');
        $country->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('Ukraine'));

        $regionMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Region');
        $regionMock->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('Kharkivs\'ka oblast\''));

        $obj->setFirstName('FirstName')
            ->setLastName('LastName')
            ->setStreet('Street')
            ->setState($regionMock)
            ->setPostalCode('12345')
            ->setCountry($country);

        $this->assertTrue(method_exists($obj, '__toString'));
        $this->assertEquals('FirstName LastName , Street   Kharkivs\'ka oblast\' , Ukraine 12345', $obj->__toString());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        $countryMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Country');
        $regionMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Region');
        return array(
            array('id', 1),
            array('lastName', 'last name'),
            array('firstName', 'first_name'),
            array('street', 'street'),
            array('street2', 'street2'),
            array('city', 'city'),
            array('state', $regionMock),
            array('postalCode', '12345'),
            array('country', $countryMock),
            array('created', new \DateTime()),
            array('updated', new \DateTime()),
        );
    }
}
