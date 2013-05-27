<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\EventDispatcher;

use Oro\Bundle\GridBundle\EventDispatcher\AbstractDatagridEvent;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;

class AbstractDatagridEventTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DATAGRID_NAME = 'test_datagrid_name';

    /**
     * @var AbstractDatagridEvent
     */
    protected $event;

    /**
     * @var DatagridInterface
     */
    protected $datagrid;

    protected function setUp()
    {
        $this->datagrid = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getName')
        );
        $this->datagrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::TEST_DATAGRID_NAME));

        $this->event = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\EventDispatcher\AbstractDatagridEvent',
            array($this->datagrid)
        );
    }

    protected function tearDown()
    {
        unset($this->datagrid);
        unset($this->event);
    }

    public function testGetDatagrid()
    {
        $this->assertEquals($this->datagrid, $this->event->getDatagrid());
    }

    public function testIsDatagridName()
    {
        $this->assertTrue($this->event->isDatagridName(self::TEST_DATAGRID_NAME));
        $this->assertFalse($this->event->isDatagridName('random_name'));
    }
}
