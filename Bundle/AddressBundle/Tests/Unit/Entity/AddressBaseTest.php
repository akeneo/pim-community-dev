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
            'id' => array('id', 1),
            'lastName' => array('lastName', 'last name'),
            'firstName' => array('firstName', 'first_name'),
            'street' => array('street', 'street'),
            'street2' => array('street2', 'street2'),
            'city' => array('city', 'city'),
            'state' => array('state', $regionMock),
            'stateText' => array('stateText', 'test state'),
            'postalCode' => array('postalCode', '12345'),
            'country' => array('country', $countryMock),
            'created' => array('created', new \DateTime()),
            'updated' => array('updated', new \DateTime()),
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
        $this->assertEquals('text state', $obj->getUniversalState());
    }

    public function testIsStateValidNoCountry()
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())
            ->method('addViolationAt');

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
            ->method('addViolationAt');

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
            ->method('addViolationAt')
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
     * @dataProvider emptyCheckPropertiesDataProvider
     * @param string $property
     * @param mixed $value
    */
    public function testIsNotEmpty($property, $value)
    {
        $obj = new AddressBase();
        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertFalse($obj->isEmpty());
    }

    /**
     * Data provider with entity properties
     *
     * @return array
     */
    public function emptyCheckPropertiesDataProvider()
    {
        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();
        $regionMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Region', array(), array('combinedCode'));
        return array(
            'lastName' => array('lastName', 'last name'),
            'firstName' => array('firstName', 'first_name'),
            'street' => array('street', 'street'),
            'street2' => array('street2', 'street2'),
            'city' => array('city', 'city'),
            'state' => array('state', $regionMock),
            'stateText' => array('stateText', 'test state'),
            'postalCode' => array('postalCode', '12345'),
            'country' => array('country', $countryMock),
        );
    }

    public function testIsNotEmptyFlexible()
    {
        $value = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue');
        $value->expects($this->once())
            ->method('getData')
            ->will($this->returnValue('not empty'));

        $obj = new AddressBase();
        $obj->addValue($value);
        $this->assertFalse($obj->isEmpty());
    }

    public function testIsEmptyFlexible()
    {
        $value = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue');
        $value->expects($this->once())
            ->method('getData');

        $obj = new AddressBase();
        $obj->addValue($value);
        $this->assertTrue($obj->isEmpty());
    }
}
