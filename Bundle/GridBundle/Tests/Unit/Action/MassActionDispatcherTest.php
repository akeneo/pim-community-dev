<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionDispatcher;

use Symfony\Component\DependencyInjection\ContainerInterface;

class MassActionDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $massAction;

    /** @var MassActionDispatcher */
    protected $dispatcher;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $container;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /**
     * setup test mocks
     */
    public function setUp()
    {
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->registry = $this->getMockBuilder('Oro\Bundle\GridBundle\Datagrid\DatagridManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dispatcher = $this->getMock(
            'Oro\Bundle\GridBundle\Action\MassAction\MassActionDispatcher',
            array('getMassActionByName', 'getDatagridQuery', 'getResultIterator', 'getMassActionHandler'),
            array(
                $this->container,
                $this->registry
            )
        );
    }

    /**
     * Test dispatch method
     */
    public function testDispatch()
    {
        $datagridName = 'user';
        $massActionName = 'delete';

        $parameters = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ParametersInterface');
        $parameters->expects($this->once())
            ->method('set');

        $datagrid = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $datagrid->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue($parameters));
        $datagrid->expects($this->once())
            ->method('applyFilters');

        $datagridManager = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridManagerInterface');
        $datagridManager->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->registry->expects($this->once())
            ->method('getDatagridManager')
            ->with($datagridName)
            ->will($this->returnValue($datagridManager));

        $massAction = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface');
        $this->dispatcher->expects($this->once())
            ->method('getMassActionByName')
            ->with($datagrid, $massActionName)
            ->will($this->returnValue($massAction));

        $proxyQuery = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');

        $this->dispatcher->expects($this->once())
            ->method('getDatagridQuery')
            ->with($datagrid, true, array('test'))
            ->will($this->returnValue($proxyQuery));

        $iterator = $this->getMock('Oro\Bundle\GridBundle\Datagrid\IterableResultInterface');
        $this->dispatcher->expects($this->once())
            ->method('getResultIterator')
            ->with($proxyQuery)
            ->will($this->returnValue($iterator));

        $handler = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionHandlerInterface');
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf('Oro\Bundle\GridBundle\Action\MassAction\MassActionMediatorInterface'))
            ->will($this->returnValue(true));

        $this->dispatcher->expects($this->once())
            ->method('getMassActionHandler')
            ->with($massAction)
            ->will($this->returnValue($handler));

        $this->dispatcher->dispatch(
            $datagridName,
            $massActionName,
            array('values' => array(
                'test'
            ))
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testDispatchInsertEmpty()
    {
        $this->dispatcher->dispatch('user', 'delete', array());
    }
}
