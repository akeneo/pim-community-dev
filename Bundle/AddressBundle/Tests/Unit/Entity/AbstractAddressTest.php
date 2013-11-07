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
        $address = $this->createAddress();

        call_user_func_array(array($address, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($address, 'get' . ucfirst($property)), array()));
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
            'organization' => array('organization', 'Oro Inc.'),
            'country' => array('country', $countryMock),
            'created' => array('createdAt', new \DateTime()),
            'updated' => array('updatedAt', new \DateTime()),
        );
    }

    public function testBeforeSave()
    {
        $address = $this->createAddress();
        $address->beforeSave();

        $this->assertNotNull($address->getCreatedAt());
        $this->assertNotNull($address->getUpdatedAt());

        $this->assertEquals($address->getCreatedAt(), $address->getUpdatedAt());
    }

    public function testGetRegionName()
    {
        $address = $this->createAddress();
        $address->setRegionText('New York');

        $this->assertEquals('New York', $address->getRegionName());

        $region = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Region')
            ->disableOriginalConstructor()
            ->setMethods(array('getName'))
            ->getMock();
        $region->expects($this->once())->method('getName')->will($this->returnValue('California'));
        $address->setRegion($region);

        $this->assertEquals('California', $address->getRegionName());
    }

    public function testGetRegionCode()
    {
        $address = $this->createAddress();

        $this->assertEquals('', $address->getRegionCode());

        $region = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Region')
            ->disableOriginalConstructor()
            ->setMethods(array('getCode'))
            ->getMock();
        $region->expects($this->once())->method('getCode')->will($this->returnValue('CA'));
        $address->setRegion($region);

        $this->assertEquals('CA', $address->getRegionCode());
    }

    public function testGetCountryName()
    {
        $address = $this->createAddress();

        $this->assertEquals('', $address->getCountryName());

        $country = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->setMethods(array('getName'))
            ->getMock();
        $country->expects($this->once())->method('getName')->will($this->returnValue('USA'));
        $address->setCountry($country);

        $this->assertEquals('USA', $address->getCountryName());
    }

    public function testGetCountryIso2()
    {
        $address = $this->createAddress();

        $this->assertEquals('', $address->getCountryIso2());

        $country = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->setMethods(array('getIso2Code'))
            ->getMock();
        $country->expects($this->once())->method('getIso2Code')->will($this->returnValue('US'));
        $address->setCountry($country);

        $this->assertEquals('US', $address->getCountryIso2());
    }

    public function testGetCountryIso3()
    {
        $address = $this->createAddress();

        $this->assertEquals('', $address->getCountryIso2());

        $country = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()
            ->setMethods(array('getIso3Code'))
            ->getMock();
        $country->expects($this->once())->method('getIso3Code')->will($this->returnValue('USA'));
        $address->setCountry($country);

        $this->assertEquals('USA', $address->getCountryIso3());
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(array $actualData, $expected)
    {
        $address = $this->createAddress();

        foreach ($actualData as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $address->$setter($value);
        }

        $this->assertTrue(method_exists($address, '__toString'));
        $this->assertEquals($expected, $address->__toString());
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
        $address = $this->createAddress();
        $region = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Region')
            ->disableOriginalConstructor()
            ->getMock();
        $address->setState($region);
        $this->assertEquals($region, $address->getState());
        $address->setStateText('text state');
        $this->assertEquals('text state', $address->getUniversalState());
    }

    public function testIsStateValidNoCountry()
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())
            ->method('addViolationAt');

        $address = $this->createAddress();
        $address->isStateValid($context);
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

        $address = $this->createAddress();
        $address->setCountry($country);
        $address->isStateValid($context);
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

        $address = $this->createAddress();
        $address->setCountry($country);
        $address->isStateValid($context);
    }

    public function testIsEmpty()
    {
        $address = $this->createAddress();
        $this->assertTrue($address->isEmpty());
    }

    /**
     * @dataProvider emptyCheckPropertiesDataProvider
     * @param string $property
     * @param mixed $value
    */
    public function testIsNotEmpty($property, $value)
    {
        $address = $this->createAddress();
        call_user_func_array(array($address, 'set' . ucfirst($property)), array($value));
        $this->assertFalse($address->isEmpty());
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
