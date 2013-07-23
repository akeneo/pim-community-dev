<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\EntityBinder;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\BoundEntity;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class EntityBinderTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_STEP = 'default_step';
    const CUSTOM_STEP = 'custom_step';
    const ENTITY_ID = 42;
    const ENTITY_CLASS = 'Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\BoundEntity';

    /**
     * @param object $entity
     * @param string|null $step
     *
     * @dataProvider bindDataProvider
     */
    public function testBind($entity, $step = null)
    {
        $entityManager = $this->getEntityManager($entity);
        $managerRegistry = $this->getManagerRegistry($entity, $entityManager);

        $workflowItem = new WorkflowItem();
        $workflowItem->setCurrentStepName(self::DEFAULT_STEP);

        $entityBinder = new EntityBinder($managerRegistry);
        $workflowItemEntity = $entityBinder->bind($workflowItem, $entity, $step);

        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowItemEntity', $workflowItemEntity);
        $this->assertEquals(self::ENTITY_CLASS, $workflowItemEntity->getEntityClass());
        $this->assertEquals(self::ENTITY_ID, $workflowItemEntity->getEntityId());
        $this->assertEquals($step ?: self::DEFAULT_STEP, $workflowItemEntity->getStepName());
        $this->assertEquals($workflowItem, $workflowItemEntity->getWorkflowItem());
        $this->assertEquals(array($workflowItemEntity), $workflowItem->getEntities()->toArray());
    }

    /**
     * @return array
     */
    public function bindDataProvider()
    {
        return array(
            'default step' => array(
                'entity' => new BoundEntity(self::ENTITY_ID),
            ),
            'custom step' => array(
                'entity' => new BoundEntity(self::ENTITY_ID),
                'step'   => self::CUSTOM_STEP,
            ),
        );
    }

    /**
     * @param mixed $entity
     * @param \PHPUnit_Framework_MockObject_MockObject $entityManager
     * @return ManagerRegistry
     */
    protected function getManagerRegistry($entity, $entityManager)
    {
        $managerRegistry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('getManagerForClass'))
            ->getMockForAbstractClass();
        if (is_object($entity) && $entity instanceof BoundEntity) {
            $managerRegistry->expects($this->once())
                ->method('getManagerForClass')
                ->with(get_class($entity))
                ->will($this->returnValue($entityManager));
        }

        return $managerRegistry;
    }

    /**
     * @param mixed $entity
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntityManager($entity)
    {
        $classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->setMethods(array('getIdentifierValues'))
            ->getMock();
        $classMetadata->expects($this->any())
            ->method('getIdentifierValues')
            ->with($this->isInstanceOf(self::ENTITY_CLASS))
            ->will(
                $this->returnCallback(
                    function (BoundEntity $entity) {
                        return array('id' => $entity->id);
                    }
                )
            );
        /** @var ClassMetadata $classMetadata */
        $classMetadata->setIdentifier(array('id'));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassMetadata'))
            ->getMock();
        if (is_object($entity) && $entity instanceof BoundEntity) {
            $entityManager->expects($this->any())
                ->method('getClassMetadata')
                ->with(self::ENTITY_CLASS)
                ->will($this->returnValue($classMetadata));
        }

        return $entityManager;
    }

    /**
     * @param mixed $entity
     */
    protected function executeBind($entity)
    {
        $entityManager = $this->getEntityManager($entity);
        $managerRegistry = $this->getManagerRegistry($entity, $entityManager);

        $workflowItem = new WorkflowItem();

        $entityBinder = new EntityBinder($managerRegistry);
        $entityBinder->bind($workflowItem, $entity);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Bind operation requires object entity
     */
    public function testBindNotAnObjectException()
    {
        $entity = array(1, 2, 3);
        $this->executeBind($entity);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException
     * @expectedExceptionMessage Entity class "DateTime" is not manageable.
     */
    public function testBindNotManageableEntityException()
    {
        $entity = new \DateTime('now');
        $this->executeBind($entity);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Bound object must have ID value.
     */
    public function testBindEmptyEntityIdException()
    {
        $entity = new BoundEntity();
        $this->executeBind($entity);
    }
}
