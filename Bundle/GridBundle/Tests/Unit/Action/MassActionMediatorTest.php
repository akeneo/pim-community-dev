<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediator;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\IterableResultInterface;

class MassActionMediatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var MassActionInterface */
    protected $massAction;

    /** @var MassActionMediator */
    protected $mediator;

    /** @var DatagridInterface */
    protected $datagrid;

    /** @var IterableResultInterface */
    protected $resultIterator;

    /**
     * setup test mocks
     */
    public function setUp()
    {
        $this->massAction = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface');
        $this->datagrid = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $this->resultIterator = $this->getMock('Oro\Bundle\GridBundle\Datagrid\IterableResultInterface');

        $this->mediator = new MassActionMediator(
            $this->massAction,
            $this->datagrid,
            $this->resultIterator,
            array()
        );
    }

    /**
     * Test getters and setters
     */
    public function testGettersAndSetters()
    {
        $this->assertEquals($this->massAction, $this->mediator->getMassAction());
        $this->assertEquals($this->resultIterator, $this->mediator->getResults());
        $this->assertEquals($this->datagrid, $this->mediator->getDatagrid());
        $this->assertEmpty($this->mediator->getData());
    }
}
