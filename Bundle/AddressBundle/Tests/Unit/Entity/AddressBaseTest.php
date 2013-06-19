<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\AddressBase;

class AddressBaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new AddressBase();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    /**
     * Data provider with entity properties
     *
     * @return array
     */
    public function propertiesDataProvider()
    {
        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $regionMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Region', array(), array('combinedCode'));
        return array(
            array('id', 1),
            array('lastName', 'last name'),
            array('firstName', 'first_name'),
            array('street', 'street'),
            array('street2', 'street2'),
            array('city', 'city'),
            array('state', $regionMock),
            array('stateText', 'test state'),
            array('postalCode', '12345'),
            array('country', $countryMock),
            array('created', new \DateTime()),
            array('updated', new \DateTime()),
        );
    }

    public function testBeforeSave()
    {
        $obj = new AddressBase();
        $obj->beforeSave();

        $this->assertNotNull($obj->getCreatedAt());
        $this->assertNotNull($obj->getUpdatedAt());

        $this->assertEquals($obj->getCreatedAt(), $obj->getUpdatedAt());
    }

    public function testToString()
    {
        $obj = new AddressBase();
        $country = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $country->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('Ukraine'));

        $regionMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Region', array(), array('combinedCode'));
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

    public function testStateText()
    {
        $obj = new AddressBase();
        $region = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Region')
            ->disableOriginalConstructor()
            ->getMock();
        $obj->setState($region);
        $this->assertEquals($region, $obj->getState());
        $obj->setStateText('text state');
        $this->assertEquals('text state', $obj->getState());
    }

    public function testIsStateValidNoCountry()
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())
            ->method('addViolationAtPath');

        $obj = new AddressBase();
        $obj->isStateValid($context);
    }

    public function testIsStateValidNoRegion()
    {
        $country = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $country->expects($this->once())
            ->method('hasRegions')
            ->will($this->returnValue(false));

        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())
            ->method('addViolationAtPath');

        $obj = new AddressBase();
        $obj->setCountry($country);
        $obj->isStateValid($context);
    }

    public function testIsStateValid()
    {
        $country = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $country->expects($this->once())
            ->method('hasRegions')
            ->will($this->returnValue(true));
        $country->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Country'));

        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())
            ->method('getPropertyPath')
            ->will($this->returnValue('test'));
        $context->expects($this->once())
            ->method('addViolationAtPath')
            ->with(
                'test.state',
                'State is required for country %country%',
                array('%country%' => 'Country')
            );

        $obj = new AddressBase();
        $obj->setCountry($country);
        $obj->isStateValid($context);
    }

    public function testIsEmpty()
    {
        $obj = new AddressBase();
        $this->assertTrue($obj->isEmpty());
    }

    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
    */
    public function testIsNotEmpty($property, $value)
    {
        $obj = new AddressBase();
        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertFalse($obj->isEmpty());
    }

    public function testIsNotEmptyFlexible()
    {
        $value = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue');

        $obj = new AddressBase();
        $obj->addValue($value);
        $this->assertFalse($obj->isEmpty());
    }
}
