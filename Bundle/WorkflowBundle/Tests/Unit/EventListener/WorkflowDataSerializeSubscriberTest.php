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
        $entity1->setWorkflowName('workflow_1');
        $entity1->setSerializedData('_old_serialized_data');
        $data1 = new WorkflowData();
        $data1->foo = 'foo';
        $entity1->setData($data1);

        $entity2 = new WorkflowItem();
        $entity2->setWorkflowName('workflow_2');
        $data2 = new WorkflowData();
        $data2->bar = 'bar';
        $entity2->setData($data2);

        $entity3 = new \stdClass();

        $entity4 = new WorkflowItem();
        $entity4->setWorkflowName('workflow_4');
        $data4 = new WorkflowData();
        $data4->foo = 'baz';
        $entity4->setData($data4);

        $entity5 = new WorkflowItem();
        $data5 = new WorkflowData(); // Leave this data not modified
        $entity5->setData($data5);

        $entity6 = new \stdClass();

        $expectedSerializedData1 = 'serialized_data_1';
        $expectedSerializedData2 = 'serialized_data_2';
        $expectedSerializedData4 = 'serialized_data_4';

        $this->serializer->expects($this->never())->method('deserialize');

        $this->serializer->expects($this->at(0))->method('setWorkflowName')
            ->with($entity1->getWorkflowName());
        $this->serializer->expects($this->at(1))->method('serialize')
            ->with($data1, 'json')->will($this->returnValue($expectedSerializedData1));

        $this->serializer->expects($this->at(2))->method('setWorkflowName')
            ->with($entity2->getWorkflowName());
        $this->serializer->expects($this->at(3))->method('serialize')
            ->with($data2, 'json')->will($this->returnValue($expectedSerializedData2));

        $this->serializer->expects($this->at(4))->method('setWorkflowName')
            ->with($entity4->getWorkflowName());
        $this->serializer->expects($this->at(5))->method('serialize')
            ->with($data4, 'json')->will($this->returnValue($expectedSerializedData4));

        $this->subscriber->onFlush(
            new OnFlushEventArgs(
                $this->getOnFlushEntityManagerMock(
                    array(
                        array(
                            'getScheduledEntityInsertions',
                            array(),
                            $this->returnValue(array($entity1, $entity2, $entity3))
                        ),
                        array(
                            'propertyChanged',
                            array($entity1, 'serializedData', $entity1->getSerializedData(), $expectedSerializedData1)
                        ),
                        array(
                            'propertyChanged',
                            array($entity2, 'serializedData', $entity2->getSerializedData(), $expectedSerializedData2)
                        ),
                        array(
                            'getScheduledEntityUpdates',
                            array(),
                            $this->returnValue(array($entity4, $entity5, $entity6))
                        ),
                        array(
                            'propertyChanged',
                            array($entity4, 'serializedData', $entity4->getSerializedData(), $expectedSerializedData4)
                        ),
                    )
                )
            )
        );

        $this->assertAttributeEquals($expectedSerializedData1, 'serializedData', $entity1);
        $this->assertAttributeEquals($expectedSerializedData2, 'serializedData', $entity2);
        $this->assertAttributeEquals($expectedSerializedData4, 'serializedData', $entity4);
        $this->assertAttributeEquals(null, 'serializedData', $entity5);
    }

    /**
     * @param array $uowExpectedCalls
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOnFlushEntityManagerMock(array $uowExpectedCalls)
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('getUnitOfWork'))
            ->disableOriginalConstructor()
            ->getMock();

        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->setMethods(array('getScheduledEntityInsertions', 'getScheduledEntityUpdates', 'propertyChanged'))
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())->method('getUnitOfWork')->will($this->returnValue($uow));

        $index = 0;
        foreach ($uowExpectedCalls as $expectedCall) {
            $expectedCall = array_pad($expectedCall, 3, null);
            list($method, $with, $stub) = $expectedCall;
            $methodExpectation = $uow->expects($this->at($index++))->method($method);
            $methodExpectation = call_user_func_array(array($methodExpectation, 'with'), $with);
            if ($stub) {
                $methodExpectation->will($stub);
            }
        }

        return $em;
    }
}
