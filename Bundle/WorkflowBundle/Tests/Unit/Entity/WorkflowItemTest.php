<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemEntity;

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

        $workflowItem->setSerializer($serializer);
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
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $workflowItem->setSerializedData('serialized_data');
        $workflowItem->getData();
    }

    public function testGetDataWithWithEmptySerializedData()
    {
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $data = $workflowItem->getData();
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Model\WorkflowData', $data);
        $this->assertTrue($data->isEmpty());
    }

    public function testSerializedData()
    {
        $this->assertAttributeEmpty('serializedData', $this->workflowItem);

        $data = 'serialized_data';

        $this->workflowItem->setSerializedData($data);
        $this->assertAttributeEquals($data, 'serializedData', $this->workflowItem);
    }

    public function testClosed()
    {
        $this->assertFalse($this->workflowItem->isClosed());
        $this->workflowItem->setClosed(true);
        $this->assertTrue($this->workflowItem->isClosed());
    }

    public function testSetEntitiesArray()
    {
        $this->assertTrue($this->workflowItem->getEntities()->isEmpty());

        $entity = new WorkflowItemEntity();
        $entities = array($entity);
        $this->workflowItem->setEntities($entities);

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->workflowItem->getEntities());
        $this->assertEquals(1, $this->workflowItem->getEntities()->count());
        $this->assertEquals($entities, $this->workflowItem->getEntities()->toArray());
        $this->assertEquals($this->workflowItem, $entity->getWorkflowItem());
    }

    public function testSetEntitiesCollection()
    {
        $this->assertTrue($this->workflowItem->getEntities()->isEmpty());

        $entity = new WorkflowItemEntity();
        $entities = new ArrayCollection(array($entity));
        $this->workflowItem->setEntities($entities);

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->workflowItem->getEntities());
        $this->assertEquals(1, $this->workflowItem->getEntities()->count());
        $this->assertEquals($entities->toArray(), $this->workflowItem->getEntities()->toArray());
        $this->assertEquals($this->workflowItem, $entity->getWorkflowItem());
    }

    public function testAddEntity()
    {
        $entityFoo = new WorkflowItemEntity();
        $entityBar = new WorkflowItemEntity();
        $this->workflowItem->addEntity($entityFoo);
        $this->workflowItem->addEntity($entityBar);

        $this->assertEquals(2, $this->workflowItem->getEntities()->count());
        $this->assertEquals($entityFoo, $this->workflowItem->getEntities()->get(0));
        $this->assertEquals($entityBar, $this->workflowItem->getEntities()->get(1));
        $this->assertEquals($this->workflowItem, $entityFoo->getWorkflowItem());
        $this->assertEquals($this->workflowItem, $entityBar->getWorkflowItem());
    }

    public function testRemoveEntity()
    {
        $entityFoo = new WorkflowItemEntity();
        $entityFoo->setEntityClass('Foo');
        $entityBar = new WorkflowItemEntity();
        $entityBar->setEntityClass('Bar');
        $entityBaz = new WorkflowItemEntity();
        $entityBaz->setEntityClass('Baz');
        $this->workflowItem->addEntity($entityFoo);
        $this->workflowItem->addEntity($entityBar);
        $this->workflowItem->addEntity($entityBaz);

        $this->workflowItem->removeEntity($entityBar);

        $this->assertEquals(2, $this->workflowItem->getEntities()->count());
        $this->assertEquals($entityFoo, $this->workflowItem->getEntities()->get(0));
        $this->assertEquals($entityBaz, $this->workflowItem->getEntities()->get(2));

        $this->workflowItem->removeEntity($entityFoo);

        $this->assertEquals(1, $this->workflowItem->getEntities()->count());
        $this->assertEquals($entityBaz, $this->workflowItem->getEntities()->get(2));

        $this->workflowItem->removeEntity($entityBaz);
        $this->assertTrue($this->workflowItem->getEntities()->isEmpty());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $entities must be an instance of Doctrine\Common\Collections\Collection or an array
     */
    public function testSetEntitiesFails()
    {
        $this->workflowItem->setEntities('roles');
    }
}
