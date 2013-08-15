<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediator;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;

class MassActionMediatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $massAction;

    /** @var MassActionMediator */
    protected $mediator;

    /** @var DatagridInterface */
    protected $datagrid;

    /**
     * setup test mocks
     */
    public function setUp()
    {
        $this->massAction = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface');
        $this->datagrid = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');

        $this->mediator = new MassActionMediator(
            $this->massAction,
            array(),
            array(),
            $this->datagrid
        );
    }

    /**
     * Test getters and setters
     */
    public function testGettersAndSetters()
    {
        $this->assertEquals($this->massAction, $this->mediator->getMassAction());
        $this->assertEquals(array(), $this->mediator->getResults());
        $this->assertEquals($this->datagrid, $this->mediator->getDatagrid());
        $this->assertEmpty($this->mediator->getData());
    }
}
