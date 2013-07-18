<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItemData;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowItemDataSerializerInterface;
use Oro\Bundle\WorkflowBundle\EventListener\WorkflowItemSerializeSubscriber;

class WorkflowItemSerializeSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowItemSerializeSubscriber
     */
    protected $subscriber;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    protected function setUp()
    {
        $this->serializer = $this->getMock('Oro\Bundle\WorkflowBundle\Serializer\WorkflowItemDataSerializerInterface');
        $this->subscriber = new WorkflowItemSerializeSubscriber($this->serializer);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array('preFlush', 'postLoad'),
            $this->subscriber->getSubscribedEvents()
        );
    }

    public function testPostLoad()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $entity = new WorkflowItem();
        $serializedData = '_serialized_data_';
        $entity->setSerializedData($serializedData);

        $expectedData = new WorkflowItemData();
        $expectedData->foo = 'foo';

        $args = new LifecycleEventArgs($entity, $em);

        $this->serializer->expects($this->never())->method('serialize');
        $this->serializer->expects($this->once())->method('deserialize')
            ->with($serializedData)->will($this->returnValue($expectedData));

        $this->subscriber->postLoad($args);

        $this->assertEquals($expectedData, $entity->getData());
    }

    public function testPostEntityNotSupported()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $entity = new \stdClass();
        $args = new LifecycleEventArgs($entity, $em);

        $this->serializer->expects($this->never())->method($this->anything());
        $this->subscriber->postLoad($args);
    }

    public function testPreFlush()
    {
        $entity1 = new WorkflowItem();
        $data1 = new WorkflowItemData();
        $data1->foo = 'foo';
        $entity1->setData($data1);

        $entity2 = new WorkflowItem();
        $data2 = new WorkflowItemData();
        $data2->bar = 'bar';
        $entity2->setData($data2);

        $entity3 = new \stdClass();

        $entity4 = new WorkflowItem();
        $data4 = new WorkflowItemData();
        $data4->foo = 'baz';
        $entity4->setData($data4);

        $entity5 = new WorkflowItem();
        $data5 = new WorkflowItemData();
        $data5->qux = 'qux';
        $entity5->setData($data5);

        $entity6 = new \stdClass();

        $expectedSerializedData1 = 'serialized_data_1';
        $expectedSerializedData2 = 'serialized_data_2';
        $expectedSerializedData4 = 'serialized_data_4';
        $expectedSerializedData5 = 'serialized_data_5';

        $this->serializer->expects($this->never())->method('deserialize');

        $this->serializer->expects($this->at(0))->method('serialize')
            ->with($data1)->will($this->returnValue($expectedSerializedData1));
        $this->serializer->expects($this->at(1))->method('serialize')
            ->with($data2)->will($this->returnValue($expectedSerializedData2));
        $this->serializer->expects($this->at(2))->method('serialize')
            ->with($data4)->will($this->returnValue($expectedSerializedData4));
        $this->serializer->expects($this->at(3))->method('serialize')
            ->with($data5)->will($this->returnValue($expectedSerializedData5));

        $this->subscriber->preFlush(
            new PreFlushEventArgs(
                $this->getPreFlushEntityManagerMock(
                    array($entity1, $entity2, $entity3),
                    array($entity4, $entity5, $entity6)
                )
            )
        );

        $this->assertEquals($expectedSerializedData1, $entity1->getSerializedData());
        $this->assertEquals($expectedSerializedData2, $entity2->getSerializedData());
        $this->assertEquals($expectedSerializedData4, $entity4->getSerializedData());
        $this->assertEquals($expectedSerializedData5, $entity5->getSerializedData());
    }

    /**
     * @param array $insertEntities
     * @param array $updateEntities
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPreFlushEntityManagerMock(array $insertEntities, array $updateEntities)
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('getUnitOfWork'))
            ->disableOriginalConstructor()
            ->getMock();

        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->setMethods(array('getScheduledEntityInsertions', 'getScheduledEntityUpdates'))
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())->method('getUnitOfWork')->will($this->returnValue($uow));
        $uow->expects($this->once())->method('getScheduledEntityInsertions')->will($this->returnValue($insertEntities));
        $uow->expects($this->once())->method('getScheduledEntityUpdates')->will($this->returnValue($updateEntities));

        return $em;
    }
}
