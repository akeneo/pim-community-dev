<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class FlexibleDatagridManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_FLEXIBLE_NAME = 'test_flexible_name';

    /**
     * @var FlexibleDatagridManager
     */
    protected $model;

    /**
     * @var array
     */
    protected $testAttributes = array('attribute_1', 'attribute_2');

    protected function setUp()
    {
        $this->model = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetFlexibleManager()
    {
        $flexibleManagerMock = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager',
            array('setLocale', 'setScope'),
            array(),
            '',
            false
        );
        $flexibleManagerMock->expects($this->once())->method('setLocale')->with($this->isType('string'));
        $flexibleManagerMock->expects($this->once())->method('setScope')->with($this->isType('string'));

        $this->assertAttributeEmpty('flexibleManager', $this->model);
        $this->model->setFlexibleManager($flexibleManagerMock);
        $this->assertAttributeEquals($flexibleManagerMock, 'flexibleManager', $this->model);
    }
}
