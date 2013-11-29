<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Entity;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowTransitionRecord;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;

class WorkflowItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowItem
     */
    protected $workflowItem;

    protected function setUp()
    {
        $this->workflowItem = new WorkflowItem();
    }

    protected function tearDown()
    {
        unset($this->workflowItem);
    }

    public function testId()
    {
        $this->assertNull($this->workflowItem->getId());
        $value = 1;
        $this->workflowItem->setId($value);
        $this->assertEquals($value, $this->workflowItem->getId());
    }

    public function testWorkflowName()
    {
        $this->assertNull($this->workflowItem->getWorkflowName());
        $value = 'example_workflow';
        $this->workflowItem->setWorkflowName($value);
        $this->assertEquals($value, $this->workflowItem->getWorkflowName());
    }

    public function testCurrentStepName()
    {
        $this->assertNull($this->workflowItem->getCurrentStepName());
        $value = 'foo';
        $this->workflowItem->setCurrentStepName($value);
        $this->assertEquals($value, $this->workflowItem->getCurrentStepName());
    }

    public function testData()
    {
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Model\WorkflowData', $this->workflowItem->getData());

        $data = new WorkflowData();
        $data['foo'] = 'Bar';

        $this->workflowItem->setData($data);
        $this->assertEquals($data, $this->workflowItem->getData());
    }

    public function testGetDataWithSerialization()
    {
        /** @var WorkflowItem $workflowItem */
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $serializedData = 'serialized_data';

        $data = new WorkflowData();
        $data->set('foo', 'bar');

        $serializer = $this->getMock('Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer');
        $serializer->expects($this->once())->method('deserialize')
            ->with($serializedData, 'Oro\Bundle\WorkflowBundle\Model\WorkflowData', 'json')
            ->will($this->returnValue($data));

        $workflowItem->setSerializer($serializer, 'json');
        $workflowItem->setSerializedData($serializedData);

        $this->assertSame($data, $workflowItem->getData());
        $this->assertSame($data, $workflowItem->getData());
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Cannot deserialize data of workflow item. Serializer is not available.
     */
    public function testGetDataWithSerializationFails()
    {
        /** @var WorkflowItem $workflowItem */
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $workflowItem->setSerializedData('serialized_data');
        $workflowItem->getData();
    }

    public function testGetDataWithWithEmptySerializedData()
    {
        /** @var WorkflowItem $workflowItem */
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $data = $workflowItem->getData();
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Model\WorkflowData', $data);
        $this->assertTrue($data->isEmpty());
    }

    public function testSetSerializedData()
    {
        $this->assertAttributeEmpty('serializedData', $this->workflowItem);
        $data = 'serialized_data';
        $this->workflowItem->setSerializedData($data);
        $this->assertAttributeEquals($data, 'serializedData', $this->workflowItem);
    }

    public function testGetSerializedData()
    {
        $this->assertNull($this->workflowItem->getSerializedData());
        $data = 'serialized_data';
        $this->workflowItem->setSerializedData($data);
        $this->assertEquals($data, $this->workflowItem->getSerializedData());
    }

    public function testGetResult()
    {
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Model\WorkflowResult', $this->workflowItem->getResult());
        $this->assertTrue($this->workflowItem->getResult()->isEmpty());
    }

    /**
     * @depends testGetResult
     */
    public function testGetResultUnserialized()
    {
        $reflection = new \ReflectionObject($this->workflowItem);
        $resultProperty = $reflection->getProperty('result');
        $resultProperty->setAccessible(true);
        $resultProperty->setValue($this->workflowItem, null);
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Model\WorkflowResult', $this->workflowItem->getResult());
        $this->assertTrue($this->workflowItem->getResult()->isEmpty());
    }

    public function testClosed()
    {
        $this->assertFalse($this->workflowItem->isClosed());
        $this->workflowItem->setClosed(true);
        $this->assertTrue($this->workflowItem->isClosed());
    }

    public function testAddBindEntity()
    {
        $entityFoo = $this->createBindEntity();
        $entityBar = $this->createBindEntity();
        $this->workflowItem->addBindEntity($entityFoo);
        $this->workflowItem->addBindEntity($entityBar);

        $this->assertEquals(2, $this->workflowItem->getBindEntities()->count());
        $this->assertEquals($entityFoo, $this->workflowItem->getBindEntities()->get(0));
        $this->assertEquals($entityBar, $this->workflowItem->getBindEntities()->get(1));
        $this->assertEquals($this->workflowItem, $entityFoo->getWorkflowItem());
        $this->assertEquals($this->workflowItem, $entityBar->getWorkflowItem());
    }

    public function testRemoveEntity()
    {
        $entityFoo = $this->createBindEntity('Foo');
        $entityBar = $this->createBindEntity('Bar');
        $entityBaz = $this->createBindEntity('Baz');
        $this->workflowItem->addBindEntity($entityFoo);
        $this->workflowItem->addBindEntity($entityBar);
        $this->workflowItem->addBindEntity($entityBaz);

        $this->workflowItem->removeBindEntity($entityBar);

        $this->assertEquals(2, $this->workflowItem->getBindEntities()->count());
        $this->assertEquals($entityFoo, $this->workflowItem->getBindEntities()->get(0));
        $this->assertEquals($entityBaz, $this->workflowItem->getBindEntities()->get(2));

        $this->workflowItem->removeBindEntity($entityFoo);

        $this->assertEquals(1, $this->workflowItem->getBindEntities()->count());
        $this->assertEquals($entityBaz, $this->workflowItem->getBindEntities()->get(2));

        $this->workflowItem->removeBindEntity($entityBaz);
        $this->assertTrue($this->workflowItem->getBindEntities()->isEmpty());
    }

    /**
     * @dataProvider syncBindEntitiesDataProvider
     *
     * @param WorkflowBindEntity[] $initialEntities
     * @param WorkflowBindEntity[] $syncEntities
     * @param WorkflowBindEntity[] $expectedEntities
     */
    public function testSyncBindEntities(array $initialEntities, array $syncEntities, array $expectedEntities)
    {
        foreach ($initialEntities as $bindEntity) {
            $this->workflowItem->addBindEntity($bindEntity);
        }

        foreach ($expectedEntities as $expectedEntity) {
            $expectedEntity->setWorkflowItem($this->workflowItem);
        }

        $this->workflowItem->syncBindEntities($syncEntities);
        $this->assertEquals($expectedEntities, $this->workflowItem->getBindEntities()->getValues());
    }

    public function syncBindEntitiesDataProvider()
    {
        return array(
            'add' => array(
                'initial' => array(),
                'sync' => array($this->createBindEntity('Foo', 1)),
                'expected' => array($this->createBindEntity('Foo', 1)),
            ),
            'remove' => array(
                'initial' => array($this->createBindEntity('Foo', 1), $this->createBindEntity('Bar', 2)),
                'sync' => array($this->createBindEntity('Bar', 2)),
                'expected' => array($this->createBindEntity('Bar', 2)),
            ),
            'add_and_remove' => array(
                'initial' => array($this->createBindEntity('Foo', 1), $this->createBindEntity('Bar', 2)),
                'sync' => array($this->createBindEntity('Foo', 1), $this->createBindEntity('Baz', 3)),
                'expected' => array($this->createBindEntity('Foo', 1), $this->createBindEntity('Baz', 3)),
            ),
        );
    }

    public function testHasBindEntity()
    {
        $entityFoo = $this->createBindEntity(null, 1);
        $entityBar = $this->createBindEntity(null, 2);

        $entityBaz = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity', array('hasSameEntity'));
        $this->assertFalse($this->workflowItem->hasBindEntity($entityBaz));

        $this->workflowItem->addBindEntity($entityFoo);
        $this->workflowItem->addBindEntity($entityBar);

        $entityBaz->expects($this->at(0))->method('hasSameEntity')->with($entityFoo)->will($this->returnValue(false));
        $entityBaz->expects($this->at(1))->method('hasSameEntity')->with($entityBar)->will($this->returnValue(false));

        $this->assertFalse($this->workflowItem->hasBindEntity($entityBaz));

        $entityBaz->expects($this->at(0))->method('hasSameEntity')->with($entityFoo)->will($this->returnValue(true));

        $this->assertTrue($this->workflowItem->hasBindEntity($entityBaz));
    }

    public function testDefinition()
    {
        $this->assertNull($this->workflowItem->getDefinition());
        $value = new WorkflowDefinition();
        $this->workflowItem->setDefinition($value);
        $this->assertEquals($value, $this->workflowItem->getDefinition());
    }

    public function testCreatedAtAndPrePersist()
    {
        $this->assertNull($this->workflowItem->getCreatedAt());
        $this->workflowItem->prePersist();
        $this->assertInstanceOf('DateTime', $this->workflowItem->getCreatedAt());

        $this->assertEquals(time(), $this->workflowItem->getCreatedAt()->getTimestamp(), '', 5);
    }

    public function testUpdatedAndPreUpdate()
    {
        $this->assertNull($this->workflowItem->getUpdatedAt());
        $this->workflowItem->preUpdate();
        $this->assertInstanceOf('DateTime', $this->workflowItem->getUpdatedAt());

        $this->assertEquals(time(), $this->workflowItem->getUpdatedAt()->getTimestamp(), '', 5);
    }

    public function testGetAddTransitionRecords()
    {
        $this->assertEmpty($this->workflowItem->getTransitionRecords()->getValues());

        $transitionRecord = new WorkflowTransitionRecord();
        $transitionRecord->setTransitionName('test_transition');

        $this->assertEquals($this->workflowItem, $this->workflowItem->addTransitionRecord($transitionRecord));
        $this->assertEquals(array($transitionRecord), $this->workflowItem->getTransitionRecords()->getValues());
        $this->assertEquals($this->workflowItem, $transitionRecord->getWorkflowItem());
    }

    /**
     * @param string $entityClass
     * @param mixed $entityId
     * @return WorkflowBindEntity
     */
    protected function createBindEntity($entityClass = null, $entityId = null)
    {
        $result = new WorkflowBindEntity();
        if ($entityClass) {
            $result->setEntityClass($entityClass);
        }
        if ($entityId) {
            $result->setEntityId($entityId);
        }
        return $result;
    }
}
