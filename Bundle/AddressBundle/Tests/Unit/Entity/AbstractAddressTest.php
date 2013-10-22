<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

class AbstractAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = $this->createAddress();

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
            'label' => array('label', 'Shipping'),
            'namePrefix' => array('namePrefix', 'name prefix'),
            'firstName' => array('firstName', 'first_name'),
            'middleName' => array('middleName', 'middle name'),
            'lastName' => array('lastName', 'last name'),
            'nameSuffix' => array('nameSuffix', 'name suffix'),
            'street' => array('street', 'street'),
            'street2' => array('street2', 'street2'),
            'city' => array('city', 'city'),
            'state' => array('state', $regionMock),
            'stateText' => array('stateText', 'test state'),
            'postalCode' => array('postalCode', '12345'),
            'country' => array('country', $countryMock),
            'created' => array('createdAt', new \DateTime()),
            'updated' => array('updatedAt', new \DateTime()),
        );
    }

    public function testBeforeSave()
    {
        $obj = $this->createAddress();
        $obj->beforeSave();

        $this->assertNotNull($obj->getCreatedAt());
        $this->assertNotNull($obj->getUpdatedAt());

        $this->assertEquals($obj->getCreatedAt(), $obj->getUpdatedAt());
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(array $actualData, $expected)
    {
        $obj = $this->createAddress();

        foreach ($actualData as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $obj->$setter($value);
        }

        $this->assertTrue(method_exists($obj, '__toString'));
        $this->assertEquals($expected, $obj->__toString());
    }

    /**
     * @return array
     */
    public function toStringDataProvider()
    {
        return array(
            array(
                array(
                    'firstName' => 'FirstName',
                    'lastName' => 'LastName',
                    'street' => 'Street',
                    'state' => $this->createMockRegion('Kharkivs\'ka oblast\''),
                    'postalCode' => '12345',
                    'country' => $this->createMockCountry('Ukraine'),
                ),
                'FirstName LastName , Street   Kharkivs\'ka oblast\' , Ukraine 12345'
            )
        );
    }

    /**
     * @param string $name
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockCountry($name)
    {
        $result = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->getMock();

        $result->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue($name));

        return $result;
    }

    /**
     * @param string $name
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockRegion($name)
    {
        $result = $this->getMock('Oro\Bundle\AddressBundle\Entity\Region', array(), array('combinedCode'));
        $result->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue($name));

        return $result;
    }

    public function testStateText()
    {
        $obj = $this->createAddress();
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

        $obj = $this->createAddress();
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

        $obj = $this->createAddress();
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

        $obj = $this->createAddress();
        $obj->setCountry($country);
        $obj->isStateValid($context);
    }

    public function testIsEmpty()
    {
        $obj = $this->createAddress();
        $this->assertTrue($obj->isEmpty());
    }

    /**
     * @dataProvider emptyCheckPropertiesDataProvider
     * @param string $property
     * @param mixed $value
    */
    public function testIsNotEmpty($property, $value)
    {
        $obj = $this->createAddress();
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

    /**
     * @dataProvider isEqualDataProvider
     *
     * @param AbstractAddress $one
     * @param mixed $two
     * @param bool $expectedResult
     */
    public function testIsEqual(AbstractAddress $one, $two, $expectedResult)
    {
        $this->assertEquals($expectedResult, $one->isEqual($two));
    }

    /**
     * @return array
     */
    public function isEqualDataProvider()
    {
        $one = $this->createAddress();

        return array(
            array($one, $one, true),
            array($this->createAddress(100), $this->createAddress(100), true),
            array($this->createAddress(), $this->createAddress(), false),
            array($this->createAddress(100), $this->createAddress(), false),
            array($this->createAddress(), null, false),
        );
    }

    /**
     * @param int|null $id
     * @return AbstractAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createAddress($id = null)
    {
        $result = $this->getMockForAbstractClass('Oro\Bundle\AddressBundle\Entity\AbstractAddress');

        if (null !== $id) {
            $result->setId($id);
        }

        return $result;
    }
}
