<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleFilter;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AbstractFlexibleFilterTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME           = 'test_name';
    const TEST_FLEXIBLE_NAME  = 'test_flexible_entity';
    /**#@-*/

    /**
     * @var AbstractFlexibleFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleRegistry;

    /**
     * @var FilterInterface
     */
    protected $parentFilter;

    protected function setUp()
    {
        $this->flexibleRegistry = $this->getMock(
            'Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry',
            array('getManager')
        );

        $this->parentFilter = $this->getMockForAbstractClass(
            'Pim\Bundle\GridBundle\Filter\FilterInterface',
            array(),
            '',
            false,
            true,
            true,
            array(
                'initialize',
                'getOption',
                'setOption',
                'getFieldName',
                'getFieldMapping',
                'getParentAssociationMappings',
                'getDefaultOptions',
                'getRenderSettings',
                'getForm',
                'getName',
                'getLabel',
                'setLabel',
                'getAssociationMapping',
                'getFieldOptions',
                'getFieldType',
                'isNullable',
            )
        );

        $this->model = $this->getMockForAbstractClass(
            'Pim\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleFilter',
            array($this->flexibleRegistry, $this->parentFilter)
        );
    }

    protected function tearDown()
    {
        unset($this->model);
        unset($this->parentFilter);
        unset($this->flexibleRegistry);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Flexible entity filter must have flexible entity name.
     */
    public function testInitializeFailsWhenNoFlexibleName()
    {
        $options = array();
        $this->parentFilter->expects($this->once())->method('initialize')->with(self::TEST_NAME, $options);
        $this->model->initialize(self::TEST_NAME, $options);
    }

    public function testInitialize()
    {
        $options = array('flexible_name' => self::TEST_FLEXIBLE_NAME);

        $flexibleManager = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->flexibleRegistry->expects($this->once())
            ->method('getManager')
            ->with(self::TEST_FLEXIBLE_NAME)
            ->will($this->returnValue($flexibleManager));

        $this->parentFilter->expects($this->once())->method('initialize')
            ->with(self::TEST_NAME, $options);
        $this->parentFilter->expects($this->once())->method('getOption')
            ->with('flexible_name')->will($this->returnValue(self::TEST_FLEXIBLE_NAME));

        $this->model->initialize(self::TEST_NAME, $options);
        $this->assertAttributeEquals($flexibleManager, 'flexibleManager', $this->model);
    }

    public function applyDataProvider()
    {
        return array(
            'use_field_mapping_entity_alias' => array(
                'value' => 'test',
                'expectParentFilterCalls' => array(
                    array('getParentAssociationMappings', array(), array('parentAssociationMappings')),
                    array('getFieldMapping', array(), array('entityAlias' => 'e')),
                    array('getFieldName', array(), 'field_name'),
                ),
                'expectEntityJoin' => array(array('parentAssociationMappings'), 'o'),
                'expectFilterArguments' => array('e', 'field_name', 'test')
            ),
            'use_entity_join_entity_alias' => array(
                'value' => 'test',
                'expectParentFilterCalls' => array(
                    array('getParentAssociationMappings', array(), array('parentAssociationMappings')),
                    array('getFieldMapping', array(), array()),
                    array('getFieldName', array(), 'field_name'),
                ),
                'expectEntityJoin' => array(array('parentAssociationMappings'), 'o'),
                'expectFilterArguments' => array('o', 'field_name', 'test')
            )
        );
    }

    /**
     * @dataProvider applyDataProvider
     */
    public function testApply(
        $value,
        array $expectParentFilterCalls,
        array $expectEntityJoin,
        array $expectFilterArguments
    ) {
        $this->addParentFilterExpectedCalls($expectParentFilterCalls);

        list($parentAssociationMappings, $entityAlias) = $expectEntityJoin;

        $proxyQuery = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');
        $proxyQuery->expects($this->once())
            ->method('entityJoin')->with($parentAssociationMappings)->will($this->returnValue($entityAlias));

        list($expectEntityAlias, $expectFieldName, $expectValue) = $expectFilterArguments;

        $this->model->expects($this->once())->method('filter')
            ->with($proxyQuery, $expectEntityAlias, $expectFieldName, $expectValue);

        $this->model->apply($proxyQuery, $value);
    }

    /**
     * @param array $expectedCalls
     */
    protected function addParentFilterExpectedCalls(array $expectedCalls)
    {
        $index = 0;
        if ($expectedCalls) {
            foreach ($expectedCalls as $expectedCall) {
                list($method, $arguments, $result) = $expectedCall;

                $methodExpectation = $this->parentFilter->expects($this->at($index++))->method($method);
                $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $arguments);
                $methodExpectation->will($this->returnValue($result));
            }
        } else {
            $this->parentFilter->expects($this->never())->method($this->anything());
        }
    }

    public function testGetDefaultOptions()
    {
        $expected = array('test');
        $this->parentFilter->expects($this->once())->method('getDefaultOptions')->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getDefaultOptions());
    }

    public function testGetRenderSettings()
    {
        $expected = array('test');
        $this->parentFilter->expects($this->once())->method('getRenderSettings')->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getRenderSettings());
    }

    public function testGetName()
    {
        $expected = 'test';
        $this->parentFilter->expects($this->once())->method('getName')->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getName());
    }

    public function testGetLabel()
    {
        $expected = 'test';
        $this->parentFilter->expects($this->once())->method('getLabel')->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getLabel());
    }

    public function testSetLabel()
    {
        $expected = 'test';
        $this->parentFilter->expects($this->once())->method('setLabel')->with($expected);
        $this->model->setLabel($expected);
    }

    public function testGetOption()
    {
        $expected = 'test';
        $name = 'name';
        $default = 'default';
        $this->parentFilter->expects($this->once())->method('getOption')
            ->with($name, $default)->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->model->getOption($name, $default));
    }

    public function testSetOption()
    {
        $name = 'name';
        $value = 'value';
        $this->parentFilter->expects($this->once())->method('setOption')
            ->with($name, $value);

        $this->model->setOption($name, $value);
    }

    public function testFieldName()
    {
        $expected = 'test';
        $this->parentFilter->expects($this->once())->method('getFieldName')->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getFieldName());
    }

    public function testGetParentAssociationMappings()
    {
        $expected = array('test');
        $this->parentFilter->expects($this->once())->method('getParentAssociationMappings')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getParentAssociationMappings());
    }

    public function testGetFieldMapping()
    {
        $expected = array('test');
        $this->parentFilter->expects($this->once())->method('getFieldMapping')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getFieldMapping());
    }

    public function testGetAssociationMapping()
    {
        $expected = array('test');
        $this->parentFilter->expects($this->once())->method('getAssociationMapping')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getAssociationMapping());
    }

    public function testGetFieldOptions()
    {
        $expected = array('test');
        $this->parentFilter->expects($this->once())->method('getFieldOptions')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getFieldOptions());
    }

    public function testGetFieldType()
    {
        $expected = array('test');
        $this->parentFilter->expects($this->once())->method('getFieldType')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getFieldType());
    }

    public function testIsNullable()
    {
        $expected = 'test';
        $this->parentFilter->expects($this->once())->method('isNullable')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->isNullable());
    }

    public function testIsActive()
    {
        $this->assertFalse($this->model->isActive());
    }
}
