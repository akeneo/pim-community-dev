<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Sorter\ORM;

use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Sorter\ORM\Sorter;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class SorterTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME = 'name';
    /**#@-*/

    /**
     * @var Sorter
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new Sorter();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testInitialize()
    {
        $fieldDescription = $this->createFieldDescription();
        $this->model->initialize($fieldDescription, SorterInterface::DIRECTION_ASC);

        $this->assertAttributeEquals($fieldDescription, 'field', $this->model);
        $this->assertAttributeEquals(SorterInterface::DIRECTION_ASC, 'direction', $this->model);
    }

    /**
     * @depends testInitialize
     */
    public function testGetField()
    {
        $fieldDescription = $this->createFieldDescription();
        $this->model->initialize($fieldDescription);
        $this->assertEquals($fieldDescription, $this->model->getField());
    }

    /**
     * @depends testInitialize
     */
    public function testGetName()
    {
        $fieldDescription = $this->createFieldDescription();
        $this->model->initialize($fieldDescription);
        $this->assertEquals(self::TEST_NAME, $this->model->getName());
    }

    /**
     * @param string|null $direction
     * @param string $expected
     *
     * @dataProvider setDirectionDataProvider
     */
    public function testSetDirection($direction = null, $expected = null)
    {
        $this->model->setDirection($direction);
        $this->assertAttributeEquals($expected, 'direction', $this->model);
    }

    /**
     * Data provider for testSetDirections
     *
     * @return array
     */
    public function setDirectionDataProvider()
    {
        return array(
            'not_sorted' => array(),
            'sorted_by_asc' => array(
                '$direction' => SorterInterface::DIRECTION_ASC,
                '$expected'  => SorterInterface::DIRECTION_ASC
            ),
            'sorted_by_desc' => array(
                '$direction' => SorterInterface::DIRECTION_DESC,
                '$expected'  => SorterInterface::DIRECTION_DESC
            ),
            'sorted_using_true_value' => array(
                '$direction' => true,
                '$expected'  => SorterInterface::DIRECTION_DESC
            ),
            'sorted_using_false_value' => array(
                '$direction' => false,
                '$expected'  => SorterInterface::DIRECTION_ASC
            )
        );
    }

    /**
     * @depends testSetDirection
     */
    public function testGetDirection()
    {
        $this->model->setDirection(SorterInterface::DIRECTION_ASC);
        $this->assertEquals(SorterInterface::DIRECTION_ASC, $this->model->getDirection());
    }

    /**
     * @depends testInitialize
     * @depends testSetDirection
     * @depends testGetDirection
     */
    public function testApply()
    {
        $expectedDirection = SorterInterface::DIRECTION_ASC;
        $expectedAssociationMapping = array('testAssociationMapping');
        $expectedFieldMapping = array('testFieldMapping');

        $fieldDescription = $this->createFieldDescription(
            self::TEST_NAME,
            $expectedAssociationMapping,
            $expectedFieldMapping
        );

        $this->createFieldDescription();
        $this->model->initialize($fieldDescription);

        $proxyQuery = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $proxyQuery->expects($this->once())
            ->method('addSortOrder')
            ->with($expectedAssociationMapping, $expectedFieldMapping, $expectedDirection);

        $this->model->apply($proxyQuery, $expectedDirection);
    }

    /**
     * Creates field description
     *
     * @param string $name
     * @param array $associationMapping
     * @param array $fieldMapping
     * @return FieldDescriptionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFieldDescription(
        $name = self::TEST_NAME,
        $associationMapping = array(),
        $fieldMapping = array()
    ) {
        $result = $this->getMockBuilder('Oro\Bundle\GridBundle\Field\FieldDescriptionInterface')
            ->getMockForAbstractClass();

        $result->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $result->expects($this->any())
            ->method('getSortParentAssociationMapping')
            ->will($this->returnValue($associationMapping));

        $result->expects($this->any())
            ->method('getSortFieldMapping')
            ->will($this->returnValue($fieldMapping));

        return $result;
    }
}
