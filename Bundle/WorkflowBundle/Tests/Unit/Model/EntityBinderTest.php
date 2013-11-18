<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;
use Oro\Bundle\WorkflowBundle\Model\EntityBinder;
use Oro\Bundle\WorkflowBundle\Model\AttributeManager;
use Oro\Bundle\WorkflowBundle\Model\StepManager;
use Oro\Bundle\WorkflowBundle\Model\TransitionManager;

class EntityBinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeManager;

    /**
     * @var EntityBinder
     */
    protected $binder;

    protected function setUp()
    {
        $this->workflowRegistry = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->setMethods(array('getWorkflow'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->workflowItem = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem');
        $this->workflowData = $this->getMock('Oro\Bundle\WorkflowBundle\Model\WorkflowData');
        $this->attributeManager = $this->getMock('Oro\Bundle\WorkflowBundle\Model\AttributeManager');
        $this->workflow = $this->getMock(
            'Oro\Bundle\WorkflowBundle\Model\Workflow',
            null,
            array(null, $this->attributeManager, null)
        );

        $this->binder = new EntityBinder($this->workflowRegistry, $this->doctrineHelper);
    }

    public function testBindEntitiesNotModified()
    {
        $this->workflowItem->expects($this->once())->method('getData')->will($this->returnValue($this->workflowData));
        $this->workflowData->expects($this->once())->method('isModified')->will($this->returnValue(false));
        $this->workflowData->expects($this->never())->method('syncBindEntities');

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

        $this->attributeManager->expects($this->once())->method('getBindEntityAttributeNames')
            ->will($this->returnValue($bindAttributeNames));

        $this->workflowData->expects($this->once())->method('getValues')->with($bindAttributeNames)
            ->will($this->returnValue(array()));

        $this->workflowItem->expects($this->once())->method('syncBindEntities')->with(array())
            ->will($this->returnValue(false));

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

        $this->attributeManager->expects($this->once())->method('getBindEntityAttributeNames')
            ->will($this->returnValue($bindAttributeNames));

        $this->workflowData->expects($this->once())->method('getValues')->with($bindAttributeNames)
            ->will($this->returnValue(array('foo' => $fooEntity, 'bar' => $barEntity)));

        $this->doctrineHelper->expects($this->at(0))->method('getEntityClass')
            ->with($fooEntity)
            ->will($this->returnValue(get_class($fooEntity)));
        $this->doctrineHelper->expects($this->at(1))->method('getEntityIdentifier')
            ->with($fooEntity)
            ->will($this->returnValue($fooIds));
        $this->doctrineHelper->expects($this->at(2))->method('getEntityClass')
            ->with($barEntity)
            ->will($this->returnValue(get_class($barEntity)));
        $this->doctrineHelper->expects($this->at(3))->method('getEntityIdentifier')
            ->with($barEntity)
            ->will($this->returnValue($barIds));

        $this->workflowItem->expects($this->once())
            ->method('syncBindEntities')
            ->with(
                $this->callback(
                    function (array $bindEntities) use ($fooEntity, $fooIds, $barEntity, $barIds) {
                        return
                            count($bindEntities) == 2 &&
                            $bindEntities[0] instanceof WorkflowBindEntity &&
                            $bindEntities[0]->getEntityId() == $fooIds['id'] &&
                            $bindEntities[0]->getEntityClass() == get_class($fooEntity) &&
                            $bindEntities[1] instanceof WorkflowBindEntity &&
                            $bindEntities[1]->getEntityId() == $barIds['id'] &&
                            $bindEntities[1]->getEntityClass() == get_class($barEntity);
                    }
                )
            )
            ->will($this->returnValue(true));

        $this->assertTrue($this->binder->bindEntities($this->workflowItem));
    }
}
