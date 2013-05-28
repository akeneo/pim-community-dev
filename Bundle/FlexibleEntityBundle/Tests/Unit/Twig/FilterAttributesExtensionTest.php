<?php

namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Twig;

use Oro\Bundle\FlexibleEntityBundle\Twig\FilterAttributesExtension;

class FilterAttributesExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterAttributesExtension
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
     * Set up test environment
     */
    public function setUp()
    {
        $this->extension = new FilterAttributesExtension();
        $this->entityMock = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible');
        $this->valuesMock = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
    }

    public function testGetName()
    {
        $this->assertEquals('oro_flexibleentity_getAttributes', $this->extension->getName());
    }

    public function testGetFilters()
    {
        $filters = $this->extension->getFilters();

        $this->assertArrayHasKey('getAttributes', $filters);
        $this->assertInstanceOf('\Twig_Filter_Method', $filters['getAttributes']);
    }

    public function testGetAttributesEmptyValuesScenario()
    {
        $this->entityMock->expects($this->exactly(2))
            ->method('getValues')
            ->will($this->returnValue($this->valuesMock));

        $this->valuesMock->expects($this->exactly(2))
            ->method('isEmpty')
            ->will($this->returnValue(true));

        $result = $this->extension->getAttributes($this->entityMock, 'test attribute');
        $this->assertInstanceOf('\PHPUnit_Framework_MockObject_MockObject',  $result);

        $result = $this->extension->getAttributes($this->entityMock, array('test'), true);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $result);
    }

    public function testGetAttributesValuesScenario()
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

        $result = $this->extension->getAttributes($this->entityMock, 'test attribute', true);
        $this->assertInstanceOf('\PHPUnit_Framework_MockObject_MockObject', $result);
        $this->assertEquals($this->valuesMock, $result);
    }

    public function testGetAttributesEmptyAttributesScenario()
    {
        $this->entityMock->expects($this->exactly(2))
            ->method('getValues')
            ->will($this->returnValue($this->valuesMock));

        $this->valuesMock->expects($this->exactly(2))
            ->method('isEmpty')
            ->will($this->returnValue(true));

        $result = $this->extension->getAttributes($this->entityMock);
        $this->assertInstanceOf('\PHPUnit_Framework_MockObject_MockObject', $result);

        $result = $this->extension->getAttributes($this->entityMock, array(), true);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $result);
    }
}
