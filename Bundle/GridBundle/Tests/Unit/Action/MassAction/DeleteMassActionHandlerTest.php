<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Action\MassAction;

use Oro\Bundle\GridBundle\Action\MassAction\DeleteMassActionHandler;

class DeleteMassActionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var DeleteMassActionHandler */
    protected $handler;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mediator;

    /**
     * setup test mocks
     */
    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        //$this->handler = new DeleteMassActionHandler($this->em, $this->translator);
        $this->handler = $this->getMock(
            'Oro\Bundle\GridBundle\Action\MassAction\DeleteMassActionHandler',
            array('getEntity'),
            array($this->em, $this->translator)
        );

        $this->mediator = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionMediatorInterface');
    }

    /**
     * handle with existing entity
     */
    public function testHandleExistingEntity()
    {
        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $result = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface');
        $result->expects($this->exactly(21))
            ->method('getRootEntity')
            ->will($this->returnValue($entity));

        $results = array_fill(0, 21, $result);

        $this->mediator
            ->expects($this->once())
            ->method('getResults')
            ->will($this->returnValue($results));

        $this->em
            ->expects($this->exactly(21))
            ->method('remove')
            ->with($this->equalTo($entity));

        $this->em
            ->expects($this->exactly(2))
            ->method('flush');

        $massAction = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface');
        $massAction->expects($this->once())
            ->method('getOption')
            ->with($this->equalTo('messages'))
            ->will($this->returnValue(array()));

        $this->mediator
            ->expects($this->once())
            ->method('getMassAction')
            ->will($this->returnValue($massAction));

        $message = 'oro.grid.mass_action.delete.success_message';
        $this->translator
            ->expects($this->once())
            ->method('transChoice')
            ->will($this->returnValue($message));

        /** @var \Oro\Bundle\GridBundle\Action\MassAction\MassActionResponse $response */
        $response = $this->handler->handle($this->mediator);

        $this->assertEquals($response->getMessage(), $message);
        $this->assertTrue($response->isSuccessful());
    }

    /**
     * handle with existing entity
     */
    public function testHandleNotExistEntity()
    {
        $entityName = 'user';
        $entity = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $this->handler
            ->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $result = $this->getMock('Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface');
        $result->expects($this->once())
            ->method('getRootEntity')
            ->will($this->returnValue(false));
        $result->expects($this->once())
            ->method('getValue')
            ->with('id')
            ->will($this->returnValue(1));

        $results = array($result);
        $this->mediator
            ->expects($this->once())
            ->method('getResults')
            ->will($this->returnValue($results));


        $datagrid = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
        $datagrid->expects($this->once())
            ->method('getIdentifierField')
            ->will($this->returnValue('id'));

        $massAction = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface');
        $massAction->expects($this->at(0))
            ->method('getOption')
            ->with($this->equalTo('entity_name'))
            ->will($this->returnValue($entityName));

        $massAction->expects($this->at(1))
            ->method('getOption')
            ->with($this->equalTo('messages'))
            ->will($this->returnValue(array()));

        $this->mediator
            ->expects($this->exactly(3))
            ->method('getMassAction')
            ->will($this->returnValue($massAction));

        $this->mediator
            ->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($datagrid));

        $this->em
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($entity));

        $this->em
            ->expects($this->once())
            ->method('flush');

        $message = 'oro.grid.mass_action.delete.success_message';
        $this->translator
            ->expects($this->once())
            ->method('transChoice')
            ->will($this->returnValue($message));

        /** @var \Oro\Bundle\GridBundle\Action\MassAction\MassActionResponse $response */
        $response = $this->handler->handle($this->mediator);

        $this->assertEquals($response->getMessage(), $message);
        $this->assertTrue($response->isSuccessful());
    }

    /**
     * Get entity test
     */
    public function testGetEntity()
    {
        $handler = $this->getMock(
            'Oro\Bundle\GridBundle\Action\MassAction\DeleteMassActionHandler',
            array('getEntityRepository'),
            array($this->em, $this->translator)
        );

        $reflection = new \ReflectionObject($handler);
        $method = $reflection->getMethod('getEntity');
        $method->setAccessible(true);

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->will($this->returnValue(new \stdClass()));

        $handler->expects($this->once())
            ->method('getEntityRepository')
            ->with($this->equalTo('user'))
            ->will($this->returnValue($repository));

        $result = $method->invoke($handler, 'user', 1);
        $this->assertInstanceOf('\stdClass', $result);
    }

    /**
     * Test exception
     *
     * @expectedException \LogicException
     */
    public function testGetEntityException()
    {
        $handler = new DeleteMassActionHandler($this->em, $this->translator);

        $reflection = new \ReflectionObject($handler);
        $method = $reflection->getMethod('getEntity');
        $method->setAccessible(true);

        $method->invoke($handler, 'user', false);
    }

    /**
     * @expectedException \LogicException
     * @dataProvider exceptionProvider
     */
    public function testGetEntityIdentifierFieldException($param)
    {
        $handler = new DeleteMassActionHandler($this->em, $this->translator);

        $reflection = new \ReflectionObject($handler);
        $method = $reflection->getMethod('getEntityIdentifierField');
        $method->setAccessible(true);

        $massAction = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface');
        $massAction->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('sdf'));

        $this->mediator
            ->expects($this->once())
            ->method('getMassAction')
            ->will($this->returnValue($massAction));

        if ($param) {
            $datagrid = $this->getMock('Oro\Bundle\GridBundle\Datagrid\DatagridInterface');
            $datagrid->expects($this->once())
                ->method('getIdentifierField')
                ->will($this->returnValue(false));
        }

        $this->mediator
            ->expects($this->once())
            ->method('getDatagrid')
            ->will($this->returnValue($param ? $datagrid : false));

        $method->invoke($handler, $this->mediator);
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetEntityName()
    {
        $handler = new DeleteMassActionHandler($this->em, $this->translator);

        $reflection = new \ReflectionObject($handler);
        $method = $reflection->getMethod('getEntityName');
        $method->setAccessible(true);

        $massAction = $this->getMock('Oro\Bundle\GridBundle\Action\MassAction\MassActionInterface');
        $massAction->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('sdf'));
        $massAction->expects($this->once())
            ->method('getOption')
            ->with('entity_name')
            ->will($this->returnValue(false));

        $this->mediator
            ->expects($this->once())
            ->method('getMassAction')
            ->will($this->returnValue($massAction));

        $method->invoke($handler, $this->mediator);
    }

    /**
     * Test getEntityRepository
     */
    public function testGetEntityRepository()
    {
        $handler = new DeleteMassActionHandler($this->em, $this->translator);

        $reflection = new \ReflectionObject($handler);
        $method = $reflection->getMethod('getEntityRepository');
        $method->setAccessible(true);

        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('user')
            ->will($this->returnValue(true));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue(true));

        $result = $method->invoke($handler, 'user');

        $this->assertTrue($result);
    }

    /**
     * Test getEntityRepository
     *
     * @expectedException \LogicException
     */
    public function testGetEntityRepositoryException()
    {
        $handler = new DeleteMassActionHandler($this->em, $this->translator);

        $reflection = new \ReflectionObject($handler);
        $method = $reflection->getMethod('getEntityRepository');
        $method->setAccessible(true);

        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('user')
            ->will($this->returnValue(false));

        $method->invoke($handler, 'user');
    }

    /**
     * Provider data for getEntityIdentifierField tests
     */
    public function exceptionProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }
}
