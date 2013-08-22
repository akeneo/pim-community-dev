<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;
use Oro\Bundle\WorkflowBundle\Model\EntityBinder;

class EntityBinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowItem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflow;

    /**
     * @var EntityBinder
     */
    protected $binder;

    protected function setUp()
    {
        $this->workflowRegistry = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->setMethods(array('getWorkflow'))
            ->disableOriginalConstructor()->getMock();
        $this->doctrineRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->workflowItem = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem');
        $this->workflowData = $this->getMock('Oro\Bundle\WorkflowBundle\Model\WorkflowData');
        $this->workflow = $this->getMock('Oro\Bundle\WorkflowBundle\Model\Workflow');
        $this->binder = new EntityBinder($this->workflowRegistry, $this->doctrineRegistry);
    }

    public function testBindEntitiesNotModified()
    {
        $this->workflowItem->expects($this->once())->method('getData')->will($this->returnValue($this->workflowData));
        $this->workflowData->expects($this->once())->method('isModified')->will($this->returnValue(false));
        $this->workflowRegistry->expects($this->never())->method($this->anything());
        $this->doctrineRegistry->expects($this->never())->method($this->anything());

        $this->assertFalse($this->binder->bindEntities($this->workflowItem));
    }

    public function testBindEntitiesNoBindAttributes()
    {
        $workflowName = 'test_workflow';
        $bindAttributeNames = array();

        $this->workflowItem->expects($this->once())->method('getData')
            ->will($this->returnValue($this->workflowData));
        $this->workflowData->expects($this->once())->method('isModified')->will($this->returnValue(true));
        $this->workflowItem->expects($this->once())->method('getWorkflowName')
            ->will($this->returnValue($workflowName));

        $this->workflowRegistry->expects($this->once())->method('getWorkflow')->with($workflowName)
            ->will($this->returnValue($this->workflow));

        $this->workflow->expects($this->once())->method('getBindEntityAttributeNames')
            ->will($this->returnValue($bindAttributeNames));

        $this->workflowData->expects($this->once())->method('getValues')->with($bindAttributeNames)
            ->will($this->returnValue(array()));

        $this->doctrineRegistry->expects($this->never())->method($this->anything());

        $this->assertFalse($this->binder->bindEntities($this->workflowItem));
    }

    public function testBindEntities()
    {
        $workflowName = 'test_workflow';
        $bindAttributeNames = array('foo', 'bar');
        $fooEntity = $this->getMock('FooEntity');
        $fooIds = array('id' => 1);
        $barEntity = $this->getMock('BarEntity');
        $barIds = array('id' => 2);

        $this->workflowItem->expects($this->at(0))->method('getData')
            ->will($this->returnValue($this->workflowData));
        $this->workflowData->expects($this->once())->method('isModified')->will($this->returnValue(true));
        $this->workflowItem->expects($this->at(1))->method('getWorkflowName')
            ->will($this->returnValue($workflowName));

        $this->workflowRegistry->expects($this->once())->method('getWorkflow')->with($workflowName)
            ->will($this->returnValue($this->workflow));

        $this->workflow->expects($this->once())->method('getBindEntityAttributeNames')
            ->will($this->returnValue($bindAttributeNames));

        $this->workflowData->expects($this->once())->method('getValues')->with($bindAttributeNames)
            ->will($this->returnValue(array('foo' => $fooEntity, 'bar' => $barEntity)));

        $this->doctrineRegistry->expects($this->at(0))->method('getManagerForClass')
            ->with(get_class($fooEntity))
            ->will($this->returnValue($this->entityManager));
        $this->doctrineRegistry->expects($this->at(1))->method('getManagerForClass')
            ->with(get_class($barEntity))
            ->will($this->returnValue($this->entityManager));

        $this->entityManager->expects($this->at(0))->method('getClassMetadata')
            ->with(get_class($fooEntity))
            ->will($this->returnValue($this->classMetadata));
        $this->entityManager->expects($this->at(1))->method('getClassMetadata')
            ->with(get_class($barEntity))
            ->will($this->returnValue($this->classMetadata));

        $this->classMetadata->expects($this->at(0))->method('getIdentifierValues')->with($fooEntity)
            ->will($this->returnValue($fooIds));
        $this->classMetadata->expects($this->at(1))->method('getIdentifierValues')->with($barEntity)
            ->will($this->returnValue($barIds));

        $this->workflowItem->expects($this->at(2))->method('hasBindEntity')
            ->with(
                $this->callback(
                    function (WorkflowBindEntity $bindEntity) use ($fooEntity, $fooIds) {
                        return $bindEntity->getEntityId() == $fooIds
                            && $bindEntity->getEntityClass() == get_class($fooEntity);
                    }
                )
            )
            ->will($this->returnValue(true));
        $this->workflowItem->expects($this->at(3))->method('hasBindEntity')
            ->with(
                $this->callback(
                    function (WorkflowBindEntity $bindEntity) use ($barEntity, $barIds) {
                        return $bindEntity->getEntityId() == $barIds
                            && $bindEntity->getEntityClass() == get_class($barEntity);
                    }
                )
            )
            ->will($this->returnValue(false));

        $this->workflowItem->expects($this->at(4))->method('addBindEntity')->with(
            $this->callback(
                function (WorkflowBindEntity $bindEntity) use ($barEntity, $barIds) {
                    return $bindEntity->getEntityId() == $barIds
                        && $bindEntity->getEntityClass() == get_class($barEntity);
                }
            )
        );

        $this->assertTrue($this->binder->bindEntities($this->workflowItem));
    }
}
