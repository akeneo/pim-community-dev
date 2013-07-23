<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\WorkflowBundle\Model\EntityBinder;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\BoundEntity;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

class EntityBinderTest extends \PHPUnit_Framework_TestCase
{
    const STEP = 'current_test_step';
    const ENTITY_ID = 42;
    const ENTITY_CLASS = 'Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\BoundEntity';

    /**
     * @param $successful
     * @param $entity
     *
     * @dataProvider bindDataProvider
     */
    public function testBind($successful, $entity)
    {
        $entityManager = $this->getEntityManager($entity);
        $managerRegistry = $this->getManagerRegistry($entity, $entityManager);

        $workflowItem = new WorkflowItem();
        $workflowItem->setCurrentStepName(self::STEP);

        $entityBinder = new EntityBinder($managerRegistry);
        $workflowItemEntity = $entityBinder->bind($workflowItem, $entity);

        if ($successful) {
            $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Entity\WorkflowItemEntity', $workflowItemEntity);
            $this->assertEquals(self::ENTITY_CLASS, $workflowItemEntity->getEntityClass());
            $this->assertEquals(self::ENTITY_ID, $workflowItemEntity->getEntityId());
            $this->assertEquals(self::STEP, $workflowItemEntity->getStepName());
            $this->assertEquals($workflowItem, $workflowItemEntity->getWorkflowItem());
        } else {
            $this->assertNull($workflowItemEntity);
        }
    }

    /**
     * @return array
     */
    public function bindDataProvider()
    {
        return array(
            'not an object' => array(
                'successful' => false,
                'entity'     => array(1, 2, 3),
            ),
            'not manageable object' => array(
                'successful' => false,
                'entity'     => new \DateTime('now'),
            ),
            'existing object' => array(
                'successful' => true,
                'entity'     => new BoundEntity(self::ENTITY_ID)
            ),
            'new object' => array(
                'successful' => true,
                'entity'     => new BoundEntity()
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
        if (is_object($entity)) {
            $managerRegistry->expects($this->once())
                ->method('getManagerForClass')
                ->with(get_class($entity))
                ->will($this->returnValue($entityManager));
        }

        return $managerRegistry;
    }

    /**
     * @param mixed $entity
     * @param boolean $isValidExtraction
     * @return null|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntityManager($entity, $isValidExtraction = true)
    {
        $entityManager = null;
        if ($entity instanceof BoundEntity) {
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
                ->setMethods(array('getClassMetadata', 'persist', 'flush'))
                ->getMock();
            $entityManager->expects($this->once())
                ->method('getClassMetadata')
                ->with(self::ENTITY_CLASS)
                ->will($this->returnValue($classMetadata));
            if (!$entity->id) {
                $entityManager->expects($this->once())
                    ->method('persist')
                    ->with($entity);
                $entityManager->expects($this->once())
                    ->method('flush')
                    ->with($entity)
                    ->will(
                        $this->returnCallback(
                            function (BoundEntity $entity) use ($isValidExtraction) {
                                if ($isValidExtraction) {
                                    $entity->id = EntityBinderTest::ENTITY_ID;
                                }
                            }
                        )
                    );
            }
        }

        return $entityManager;
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Can't extract entity ID from class Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Stub\BoundEntity
     */
    // @codingStandardsIgnoreEnd
    public function testBindExtractIdException()
    {
        $entity = new BoundEntity();

        $entityManager = $this->getEntityManager($entity, false);
        $managerRegistry = $this->getManagerRegistry($entity, $entityManager);

        $workflowItem = new WorkflowItem();

        $entityBinder = new EntityBinder($managerRegistry);
        $entityBinder->bind($workflowItem, $entity);
    }
}
