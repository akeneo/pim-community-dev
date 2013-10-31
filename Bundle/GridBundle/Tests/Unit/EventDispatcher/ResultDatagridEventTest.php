<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\EventDispatcher;

use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;

class ResultDatagridEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResultDatagridEvent
     */
    protected $event;

    /**
     * @var array
     */
    protected $testRows = array(
        array(1, 2, 3),
        array(7, 8, 9),
    );

    protected function setUp()
    {
        $datagrid = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridInterface',
            array(),
            '',
            false
        );

        $this->event = new ResultDatagridEvent($datagrid);
    }

    protected function tearDown()
    {
        unset($this->event);
    }

    public function testSetRows()
    {
        $this->event->setRows($this->testRows);
        $this->assertAttributeEquals($this->testRows, 'rows', $this->event);
    }

    public function testGetRows()
    {
        $this->event->setRows($this->testRows);
        $this->assertEquals($this->testRows, $this->event->getRows());
    }
}
