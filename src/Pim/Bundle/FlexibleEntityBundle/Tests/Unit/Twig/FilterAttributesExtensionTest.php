<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Twig;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\FlexibleEntityBundle\Twig\FilterAttributesExtension;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
    protected function setUp()
    {
        $this->extension = new FilterAttributesExtension();
        $this->entityMock = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible');
        $this->valuesMock = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
    }

    public function testGetName()
    {
        $this->assertEquals('pim_flexibleentity_getAttributes', $this->extension->getName());
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
        $this->assertEquals($this->valuesMock, $result);

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
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $result);
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

    public function testFilterScenario()
    {
        $valueMock1 = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface');
        $attributeMock1 = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute');
        $valueMock2 = clone $valueMock1;
        $attributeMock2 = clone $attributeMock1;

        $valueMock1->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock1));

        $valueMock2->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock2));

        $attributeMock1->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('codeNeeded'));

        $attributeMock2->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('codeNotNeeded'));

        $collection = new ArrayCollection(
            array(
                $valueMock1, $valueMock2
            )
        );

        $this->entityMock->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($collection));

        $result = $this->extension->getAttributes($this->entityMock, array('codeNeeded'));
        $this->assertCount(1, $result);
        $this->assertEquals($valueMock1, $result->first());
    }

    public function testFilterSkipScenario()
    {
        $valueMock1 = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface');
        $attributeMock1 = $this->getMock('Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute');
        $valueMock2 = clone $valueMock1;
        $attributeMock2 = clone $attributeMock1;

        $valueMock1->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock1));

        $valueMock2->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attributeMock2));

        $attributeMock1->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('codeNeeded'));

        $attributeMock2->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('codeNotNeeded'));

        $collection = new ArrayCollection(
            array(
                $valueMock1, $valueMock2
            )
        );

        $this->entityMock->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue($collection));

        $result = $this->extension->getAttributes($this->entityMock, array('codeNotNeeded'), true);
        $this->assertCount(1, $result);
        $this->assertEquals($valueMock1, $result->first());
    }
}
