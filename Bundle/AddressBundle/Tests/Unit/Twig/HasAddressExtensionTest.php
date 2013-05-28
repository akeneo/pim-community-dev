<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Twig;

use Oro\Bundle\AddressBundle\Twig\HasAddressExtension;

class HasAddressExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HasAddressExtension
     */
    private $extension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $valuesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $valueMock;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->extension = new HasAddressExtension();
        $this->entityMock = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible');
        $this->valuesMock = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
        $this->valueMock = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface');
    }

    public function testGetName()
    {
        $this->assertEquals('oro_address_hasAddress', $this->extension->getName());
    }

    public function testGetFilters()
    {
        $filters = $this->extension->getFilters();

        $this->assertArrayHasKey('hasAddress', $filters);
        $this->assertInstanceOf('\Twig_Filter_Method', $filters['hasAddress']);
    }

    public function testHasAddressTrueScenario()
    {
        $this->valueMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(true));

        $this->entityMock->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('address'))
            ->will($this->returnValue($this->valueMock));

        $this->assertTrue($this->extension->hasAddress($this->entityMock, 'address'));
    }

    public function testHasAddressFalseScenario()
    {
        $this->valueMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(null));

        $this->entityMock->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('address'))
            ->will($this->returnValue($this->valueMock));

        $this->assertFalse($this->extension->hasAddress($this->entityMock, 'address'));
    }

    public function testHasAddressTrueScenarioWithoutCode()
    {
        $this->entityMock->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($this->valuesMock));

        $this->valuesMock->expects($this->once())
            ->method('filter')
            ->will($this->returnValue($this->valuesMock));
        $this->valuesMock->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(false));

        $this->assertTrue($this->extension->hasAddress($this->entityMock));
    }

    public function testHasAddressFalseScenarioWithoutCode()
    {
        $this->entityMock->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($this->valuesMock));

        $this->valuesMock->expects($this->once())
            ->method('filter')
            ->will($this->returnValue($this->valuesMock));
        $this->valuesMock->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(true));

        $this->assertFalse($this->extension->hasAddress($this->entityMock));
    }
}
