<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action;

use Doctrine\ORM\Query\Expr;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

class MassActionDispatcherTest extends \PHPUnit_Framework_TestCase
{
    const TEST_IDENTIFIER_FIELDNAME = 'id';
    const TEST_BUFFER_SIZE           = 30;

    /** @var \PHPUnit_Framework_MockObject_MockBuilder */
    protected $dispatcher;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $container;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $qb;

    /**
     * setup test mocks
     */
    public function setUp()
    {
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->registry = $this->getMockBuilder('Oro\Bundle\GridBundle\Datagrid\DatagridManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dispatcher = $this->getMockBuilder(
            'Oro\Bundle\GridBundle\Action\MassAction\MassActionDispatcher'
        )->setConstructorArgs(
            array(
                $this->container,
                $this->registry
            )
        );

        $this->qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset($this->dispatcher);
        unset($this->registry);
        unset($this->container);
    }

    /**
     * Test dispatch method
     */
    public function testDispatch()
    {
        $datagridName = 'user';
        $massActionName = 'delete';

        $dispatcher = $this->getDispatcherObject(
            array('getMassActionByName', 'getDatagridQuery', 'getResultIterator', 'getMassActionHandler')
        );

        $filterData = array('someFilter' => 'someValue');

        $parameters = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ParametersInterface');
        $parameters->expects($this->once())
            ->method('set')->with(ParametersInterface::FILTER_PARAMETERS, $filterData);

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
        $dispatcher->expects($this->once())
            ->method('getMassActionByName')
            ->with($datagrid, $massActionName)
            ->will($this->returnValue($massAction));

        $proxyQuery = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface');

        $dispatcher->expects($this->once())
            ->method('getDatagridQuery')
            ->with($datagrid, true, array('test'))
            ->will($this->returnValue($proxyQuery));

        $iterator = $this->getMock('Oro\Bundle\GridBundle\Datagrid\IterableResultInterface');
        $dispatcher->expects($this->once())
            ->method('getResultIterator')
            ->with($proxyQuery)
            ->will($this->returnValue($iterator));

        $handler = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionHandlerInterface');
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf('Oro\Bundle\GridBundle\Action\MassAction\MassActionMediatorInterface'))
            ->will($this->returnValue(true));

        $dispatcher->expects($this->once())
            ->method('getMassActionHandler')
            ->with($massAction)
            ->will($this->returnValue($handler));

        $dispatcher->dispatch(
            $datagridName,
            $massActionName,
            array(
                'values' => array(
                    'test'
                ),
                'inset' => true,
                'filters' => $filterData,
            )
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testDispatchInsertEmpty()
    {
        $dispatcher = $this->getDispatcherObject(
            array('getMassActionByName', 'getDatagridQuery', 'getResultIterator', 'getMassActionHandler')
        );
        $dispatcher->dispatch('user', 'delete', array());
    }

    public function testGetDatagridQuery()
    {
        $dispatcher = $this->getDispatcherObject(array('getIdentifierExpression'));
        $query = $this->getProxyQueryObject();

        $datagrid = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $datagrid->expects($this->once())->method('getQuery')
            ->will($this->returnValue($query));

        $dispatcher->expects($this->once())->method('getIdentifierExpression')->with($datagrid)
            ->will($this->returnValue(self::TEST_IDENTIFIER_FIELDNAME));

        self::callProtectedMethod($dispatcher, 'getDatagridQuery', array($datagrid, true, array(1, 2)));
    }

    public function testGetMassActionByName()
    {

    }

    public function testGetResultIterator()
    {
        $dispatcher = $this->getDispatcherObject();
        $query = $this->getProxyQueryObject();

        $result = self::callProtectedMethod($dispatcher, 'getResultIterator', array($query, self::TEST_BUFFER_SIZE));

        $this->assertInstanceOf('Oro\Bundle\GridBundle\Datagrid\ORM\IterableResult', $result);
        $this->assertAttributeEquals(self::TEST_BUFFER_SIZE, 'bufferSize', $result);
    }

    /**
     * @return ProxyQuery
     */
    protected function getProxyQueryObject()
    {
        $this->qb->expects($this->any())->method('expr')
            ->will($this->returnValue(new Expr()));
        $proxyQuery = new ProxyQuery($this->qb);

        return $proxyQuery;
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDispatcherObject($methods = array())
    {
        $this->dispatcher->setMethods($methods);

        return $this->dispatcher->getMock();
    }

    /**
     * @param mixed $obj
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    protected static function callProtectedMethod($obj, $methodName, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
