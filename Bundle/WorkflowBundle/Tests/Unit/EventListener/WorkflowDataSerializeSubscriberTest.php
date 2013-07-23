<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowData;
use Oro\Bundle\WorkflowBundle\EventListener\WorkflowDataSerializeSubscriber;

class WorkflowDataSerializeSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowDataSerializeSubscriber
     */
    protected $subscriber;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    protected function setUp()
    {
        $this->serializer = $this->getMock('Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer');
        $this->subscriber = new WorkflowDataSerializeSubscriber($this->serializer);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array('onFlush', 'postLoad'),
            $this->subscriber->getSubscribedEvents()
        );
    }

    public function testPostLoad()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $entity = new WorkflowItem();

        $args = new LifecycleEventArgs($entity, $em);

        $this->serializer->expects($this->never())->method('serialize');
        $this->serializer->expects($this->never())->method('deserialize');

        $this->subscriber->postLoad($args);

        $this->assertAttributeSame($this->serializer, 'serializer', $entity);
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

    public function testOnFlush()
    {
        $entity1 = new WorkflowItem();
        $data1 = new WorkflowData();
        $data1->foo = 'foo';
        $entity1->setData($data1);

        $entity2 = new WorkflowItem();
        $data2 = new WorkflowData();
        $data2->bar = 'bar';
        $entity2->setData($data2);

        $entity3 = new \stdClass();

        $entity4 = new WorkflowItem();
        $data4 = new WorkflowData();
        $data4->foo = 'baz';
        $entity4->setData($data4);

        $entity5 = new WorkflowItem();
        $data5 = new WorkflowData();
        $data5->qux = 'qux';
        $entity5->setData($data5);

        $entity6 = new \stdClass();

        $expectedSerializedData1 = 'serialized_data_1';
        $expectedSerializedData2 = 'serialized_data_2';
        $expectedSerializedData4 = 'serialized_data_4';
        $expectedSerializedData5 = 'serialized_data_5';

        $this->serializer->expects($this->never())->method('deserialize');

        $this->serializer->expects($this->at(0))->method('serialize')
            ->with($data1, 'json')->will($this->returnValue($expectedSerializedData1));
        $this->serializer->expects($this->at(1))->method('serialize')
            ->with($data2, 'json')->will($this->returnValue($expectedSerializedData2));
        $this->serializer->expects($this->at(2))->method('serialize')
            ->with($data4, 'json')->will($this->returnValue($expectedSerializedData4));
        $this->serializer->expects($this->at(3))->method('serialize')
            ->with($data5, 'json')->will($this->returnValue($expectedSerializedData5));

        $this->subscriber->onFlush(
            new OnFlushEventArgs(
                $this->getOnFlushEntityManagerMock(
                    array($entity1, $entity2, $entity3),
                    array($entity4, $entity5, $entity6)
                )
            )
        );

        $this->assertAttributeEquals($expectedSerializedData1, 'serializedData', $entity1);
        $this->assertAttributeEquals($expectedSerializedData2, 'serializedData', $entity2);
        $this->assertAttributeEquals($expectedSerializedData4, 'serializedData', $entity4);
        $this->assertAttributeEquals($expectedSerializedData5, 'serializedData', $entity5);
    }

    /**
     * @param array $insertEntities
     * @param array $updateEntities
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOnFlushEntityManagerMock(array $insertEntities, array $updateEntities)
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
