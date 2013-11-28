<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\Step;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\AttributeManager;
use Oro\Bundle\WorkflowBundle\Model\StepManager;
use Oro\Bundle\WorkflowBundle\Model\TransitionManager;

class WorkflowManagerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_WORKFLOW_NAME = 'test_workflow';

    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $workflowRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->setMethods('getManager')
            ->getMockForAbstractClass();

        $this->workflowRegistry = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('getWorkflow', 'getWorkflowsByEntityClass'))
            ->getMock();

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\DoctrineHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('getEntityIdentifier'))
            ->getMock();

        $this->workflowManager = new WorkflowManager(
            $this->registry,
            $this->workflowRegistry,
            $this->doctrineHelper
        );
    }

    protected function tearDown()
    {
        unset($this->registry);
        unset($this->workflowRegistry);
        unset($this->doctrineHelper);
        unset($this->workflowManager);
    }

    public function testGetStartTransitions()
    {
        $startTransition = new Transition();
        $startTransition->setName('start_transition');
        $startTransition->setStart(true);

        $startTransitions = new ArrayCollection(array($startTransition));
        $workflow = $this->createWorkflow('test_workflow', array(), $startTransitions->toArray());
        $this->assertEquals($startTransitions, $this->workflowManager->getStartTransitions($workflow));
    }

    public function testGetTransitionsByWorkflowItem()
    {
        $workflowName = 'test_workflow';

        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName($workflowName);

        $transition = new Transition();
        $transition->setName('test_transition');

        $transitions = new ArrayCollection(array($transition));

        $workflow = $this->createWorkflow($workflowName);
        $workflow->expects($this->once())
            ->method('getTransitionsByWorkflowItem')
            ->with($workflowItem)
            ->will($this->returnValue($transitions));

        $this->workflowRegistry->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowName)
            ->will($this->returnValue($workflow));

        $this->assertEquals(
            $transitions,
            $this->workflowManager->getTransitionsByWorkflowItem($workflowItem)
        );
    }

    public function testIsTransitionAvailable()
    {
        $workflowName = 'test_workflow';

        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName($workflowName);

        $errors = new ArrayCollection();

        $transition = new Transition();
        $transition->setName('test_transition');

        $workflow = $this->createWorkflow($workflowName);
        $workflow->expects($this->once())
            ->method('isTransitionAvailable')
            ->with($workflowItem, $transition, $errors)
            ->will($this->returnValue(true));

        $this->workflowRegistry->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowName)
            ->will($this->returnValue($workflow));

        $this->assertTrue($this->workflowManager->isTransitionAvailable($workflowItem, $transition, $errors));
    }

    public function testIsStartTransitionAvailable()
    {
        $workflowName = 'test_workflow';
        $errors = new ArrayCollection();
        $entity = new \DateTime('now');

        $entityAttribute = new Attribute();
        $entityAttribute->setName('entity_attribute');
        $entityAttribute->setType('entity');
        $entityAttribute->setOptions(array('class' => 'DateTime', 'managed_entity' => true));

        $stringAttribute = new Attribute();
        $stringAttribute->setName('other_attribute');
        $stringAttribute->setType('string');

        $transition = 'test_transition';

        $workflow = $this->createWorkflow($workflowName, array($entityAttribute, $stringAttribute));
        $workflow->expects($this->once())
            ->method('isStartTransitionAvailable')
            ->with($transition, array('entity_attribute' => $entity), $errors)
            ->will($this->returnValue(true));

        $this->workflowRegistry->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowName)
            ->will($this->returnValue($workflow));

        $this->assertTrue(
            $this->workflowManager->isStartTransitionAvailable($workflowName, $transition, $entity, $errors)
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\UnknownAttributeException
     * @expectedExceptionMessage Can't find attribute for managed entity in workflow "empty_workflow"
     */
    public function testGetWorkflowDataUnknownAttribute()
    {
        $workflowName = 'empty_workflow';
        $transition = 'test_transition';
        $entity = new \DateTime('now');

        $workflow = $this->createWorkflow($workflowName);
        $this->workflowRegistry->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowName)
            ->will($this->returnValue($workflow));
        $this->workflowManager->isStartTransitionAvailable($workflowName, $transition, $entity);
    }

    public function testStartWorkflow()
    {
        $transition = 'test_transition';
        $workflowData = array('key' => 'value');
        $workflowItem = new WorkflowItem();
        $workflowItem->getData()->add($workflowData);

        $workflow = $this->createWorkflow();
        $workflow->expects($this->once())
            ->method('start')
            ->with($workflowData, $transition)
            ->will($this->returnValue($workflowItem));

        $entityManager = $this->createEntityManager();
        $entityManager->expects($this->once())
            ->method('beginTransaction');
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($workflowItem);
        $entityManager->expects($this->once())
            ->method('flush');
        $entityManager->expects($this->once())
            ->method('commit');

        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($entityManager));

        $actualWorkflowItem = $this->workflowManager->startWorkflow($workflow, null, $transition, $workflowData);

        $this->assertEquals($workflowItem, $actualWorkflowItem);
        $this->assertEquals($workflowData, $actualWorkflowItem->getData()->getValues());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Start workflow exception message
     */
    public function testStartWorkflowException()
    {
        $entityManager = $this->createEntityManager();
        $entityManager->expects($this->once())
            ->method('beginTransaction');
        $entityManager->expects($this->once())
            ->method('persist')
            ->will($this->throwException(new \Exception('Start workflow exception message')));
        $entityManager->expects($this->once())
            ->method('rollback');

        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($entityManager));

        $this->workflowManager->startWorkflow($this->createWorkflow(), null, 'test_transition');
    }

    public function testTransit()
    {
        $transition = 'test_transition';
        $workflowName = 'test_workflow';

        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName($workflowName);

        $workflow = $this->createWorkflow($workflowName);
        $workflow->expects($this->once())
            ->method('transit')
            ->with($workflowItem, $transition);

        $this->workflowRegistry->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowName)
            ->will($this->returnValue($workflow));

        $entityManager = $this->createEntityManager();
        $entityManager->expects($this->once())
            ->method('beginTransaction');
        $entityManager->expects($this->once())
            ->method('flush');
        $entityManager->expects($this->once())
            ->method('commit');

        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($entityManager));

        $this->assertEmpty($workflowItem->getUpdatedAt());
        $this->workflowManager->transit($workflowItem, $transition);
        $this->assertNotEmpty($workflowItem->getUpdatedAt());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Transit exception message
     */
    public function testTransitException()
    {
        $workflowName = 'test_workflow';

        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName($workflowName);

        $this->workflowRegistry->expects($this->once())
            ->method('getWorkflow')
            ->with($workflowName)
            ->will($this->returnValue($this->createWorkflow($workflowName)));

        $entityManager = $this->createEntityManager();
        $entityManager->expects($this->once())
            ->method('beginTransaction');
        $entityManager->expects($this->once())
            ->method('flush')
            ->will($this->throwException(new \Exception('Transit exception message')));
        $entityManager->expects($this->once())
            ->method('rollback');

        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($entityManager));

        $this->workflowManager->transit($workflowItem, 'test_transition');
    }

    /**
     * @dataProvider workflowNameDataProvider
     * @param string|null $requiredWorkflowName
     */
    public function testGetApplicableWorkflows($requiredWorkflowName)
    {
        // mocks for entity metadata
        $entity = new \DateTime('now');
        $entityClass = get_class($entity);
        $entityId = 1;

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityIdentifier')
            ->with($entity)
            ->will($this->returnValue($entityId));

        // mocks for workflows:
        // - without workflow items
        // - single managed entity with workflow items
        // - multiple managed entity with workflow items
        $singleEntityAttribute = new Attribute();
        $singleEntityAttribute->setOptions(array('class' => $entityClass));
        $multipleEntityAttribute = new Attribute();
        $multipleEntityAttribute->setOptions(array('class' => $entityClass, 'multiple' => true));

        $newWorkflow = $this->createWorkflow('new_workflow', array($singleEntityAttribute));
        $usedSingleWorkflow = $this->createWorkflow('used_single_workflow', array($singleEntityAttribute));
        $usedMultipleWorkflow = $this->createWorkflow('used_multiple_workflow', array($multipleEntityAttribute));
        $allowedWorkflows = array($newWorkflow, $usedSingleWorkflow, $usedMultipleWorkflow);

        if ($requiredWorkflowName) {
            $this->workflowRegistry->expects($this->exactly(2))
                ->method('getWorkflow')
                ->with($requiredWorkflowName)
                ->will($this->returnValue($newWorkflow));
            // expected workflows (single managed entity with existing workflow items is not allowed)
            $expectedWorkflows = array(
                $newWorkflow->getName() => $newWorkflow
            );
        } else {
            // expected workflows (single managed entity with existing workflow items is not allowed)
            $expectedWorkflows = array(
                $newWorkflow->getName() => $newWorkflow,
                $usedMultipleWorkflow->getName() => $usedMultipleWorkflow,
            );
            $this->workflowRegistry->expects($this->any())
                ->method('getWorkflowsByEntityClass')
                ->with($entityClass)
                ->will($this->returnValue($allowedWorkflows));
        }

        // mocks for workflow items
        $workflowItems = array(
            $this->createWorkflowItem($usedSingleWorkflow->getName()),
            $this->createWorkflowItem($usedMultipleWorkflow->getName()),
        );

        $workflowItemsRepository =
            $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository')
                ->disableOriginalConstructor()
                ->setMethods(array('findByEntityMetadata'))
                ->getMock();
        $workflowItemsRepository->expects($this->any())
            ->method('findByEntityMetadata')
            ->with($entityClass, $entityId, $requiredWorkflowName)
            ->will($this->returnValue($workflowItems));
        $this->registry->expects($this->any())
            ->method('getRepository')
            ->with('OroWorkflowBundle:WorkflowItem')
            ->will($this->returnValue($workflowItemsRepository));

        // with automatic workflow item extraction
        $this->assertEquals(
            $expectedWorkflows,
            $this->workflowManager->getApplicableWorkflows($entity, null, $requiredWorkflowName)
        );

        // with manual workflow item setting
        $this->assertEquals(
            $expectedWorkflows,
            $this->workflowManager->getApplicableWorkflows($entity, $workflowItems, $requiredWorkflowName)
        );
    }

    /**
     * @dataProvider workflowNameDataProvider
     * @param string|null $requiredWorkflowName
     */
    public function testGetWorkflowItemsByEntity($requiredWorkflowName)
    {
        $entity = new \DateTime('now');
        $entityClass = get_class($entity);
        $entityId = 1;

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityIdentifier')
            ->with($entity)
            ->will($this->returnValue($entityId));

        $workflowItems = array($this->createWorkflowItem());

        $workflowItemsRepository =
            $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository')
                ->disableOriginalConstructor()
                ->setMethods(array('findByEntityMetadata'))
                ->getMock();
        $workflowItemsRepository->expects($this->any())
            ->method('findByEntityMetadata')
            ->with($entityClass, $entityId, $requiredWorkflowName)
            ->will($this->returnValue($workflowItems));
        $this->registry->expects($this->any())
            ->method('getRepository')
            ->with('OroWorkflowBundle:WorkflowItem')
            ->will($this->returnValue($workflowItemsRepository));

        $this->assertEquals(
            $workflowItems,
            $this->workflowManager->getWorkflowItemsByEntity($entity, $requiredWorkflowName)
        );
    }

    public function workflowNameDataProvider()
    {
        return array(
            array(null),
            array('test_workflow')
        );
    }

    /**
     * @param mixed $workflowIdentifier
     * @dataProvider getWorkflowDataProvider
     */
    public function testGetWorkflow($workflowIdentifier)
    {
        $expectedWorkflow = $this->createWorkflow(self::TEST_WORKFLOW_NAME);

        if ($workflowIdentifier instanceof Workflow) {
            $this->workflowRegistry->expects($this->never())
                ->method('getWorkflow');
        } else {
            $this->workflowRegistry->expects($this->any())
                ->method('getWorkflow')
                ->with(self::TEST_WORKFLOW_NAME)
                ->will($this->returnValue($expectedWorkflow));
        }

        $this->assertEquals($expectedWorkflow, $this->workflowManager->getWorkflow($workflowIdentifier));
    }

    /**
     * @return array
     */
    public function getWorkflowDataProvider()
    {
        return array(
            'string' => array(
                'workflowIdentifier' => self::TEST_WORKFLOW_NAME,
            ),
            'workflow item' => array(
                'workflowIdentifier' => $this->createWorkflowItem(self::TEST_WORKFLOW_NAME),
            ),
            'workflow' => array(
                'workflowIdentifier' => $this->createWorkflow(self::TEST_WORKFLOW_NAME),
            ),
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Can't find workflow by given identifier.
     */
    public function testGetWorkflowCantFind()
    {
        $incorrectIdentifier = null;
        $this->workflowManager->getWorkflow($incorrectIdentifier);
    }

    public function testIsAllManagedEntitiesSpecified()
    {
        $managedAttributeName = 'entity';

        $managedAttribute = new Attribute();
        $managedAttribute->setName($managedAttributeName);

        $workflow = $this->createWorkflow(self::TEST_WORKFLOW_NAME, array($managedAttribute));
        $this->workflowRegistry->expects($this->any())
            ->method('getWorkflow')
            ->with(self::TEST_WORKFLOW_NAME)
            ->will($this->returnValue($workflow));

        $validWorkflowItem = $this->createWorkflowItem();
        $validWorkflowItem->getData()->set($managedAttributeName, new \DateTime());
        $this->assertTrue($this->workflowManager->isAllManagedEntitiesSpecified($validWorkflowItem));

        $invalidWorkflowItem = $this->createWorkflowItem();
        $this->assertFalse($this->workflowManager->isAllManagedEntitiesSpecified($invalidWorkflowItem));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEntityManager()
    {
        return $this->getMockBuilder('Doctrine\Orm\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('beginTransaction', 'persist', 'flush', 'commit', 'rollback'))
            ->getMock();
    }

    /**
     * @param string $workflowName
     * @return WorkflowItem
     */
    protected function createWorkflowItem($workflowName = self::TEST_WORKFLOW_NAME)
    {
        $workflowItem = new WorkflowItem();
        $workflowItem->setWorkflowName($workflowName);

        return $workflowItem;
    }

    /**
     * @param string $name
     * @param array $entityAttributes
     * @param array $startTransitions
     * @return Workflow|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createWorkflow(
        $name = self::TEST_WORKFLOW_NAME,
        array $entityAttributes = array(),
        array $startTransitions = array()
    ) {
        $attributeManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\AttributeManager')
            ->setMethods(array('getManagedEntityAttributes'))
            ->getMock();
        $attributeManager->expects($this->any())
            ->method('getManagedEntityAttributes')
            ->will($this->returnValue($entityAttributes));

        $transitionManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\TransitionManager')
            ->setMethods(array('getStartTransitions'))
            ->getMock();
        $transitionManager->expects($this->any())
            ->method('getStartTransitions')
            ->will($this->returnValue(new ArrayCollection($startTransitions)));

        $worklflow = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Workflow')
            ->setConstructorArgs(array(null, $attributeManager, $transitionManager))
            ->setMethods(
                array(
                    'isTransitionAvailable',
                    'isStartTransitionAvailable',
                    'getTransitionsByWorkflowItem',
                    'start',
                    'transit'
                )
            )
            ->getMock();

        /** @var Workflow $worklflow */
        $worklflow->setName($name);

        return $worklflow;
    }
}
