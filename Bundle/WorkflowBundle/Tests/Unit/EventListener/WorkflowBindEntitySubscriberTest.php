<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\PreFlushEventArgs;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\EventListener\WorkflowBindEntitySubscriber;

class WorkflowBindEntitySubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowBindEntitySubscriber
     */
    protected $subscriber;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $binder;

    protected function setUp()
    {
        $this->binder = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\EntityBinder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriber = new WorkflowBindEntitySubscriber($this->binder);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array('preFlush'), $this->subscriber->getSubscribedEvents());
    }

    public function testPreFlush()
    {
        $entity1 = new WorkflowItem();
        $entity1->setWorkflowName('workflow_1');

        $entity2 = new WorkflowItem();
        $entity2->setWorkflowName('workflow_2');

        $entity3 = new \stdClass();

        $entity4 = new WorkflowItem();
        $entity4->setWorkflowName('workflow_4');

        $entity5 = new \stdClass();

        $entity6 = new WorkflowItem();
        $entity6->setWorkflowName('workflow_6');

        $this->binder->expects($this->at(0))->method('bindEntities')->with($entity1);
        $this->binder->expects($this->at(1))->method('bindEntities')->with($entity2);
        $this->binder->expects($this->at(2))->method('bindEntities')->with($entity4);
        $this->binder->expects($this->at(3))->method('bindEntities')->with($entity6);

        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->setMethods(array('getScheduledEntityInsertions', 'getScheduledEntityUpdates', 'propertyChanged'))
            ->disableOriginalConstructor()
            ->getMock();

        $uow->expects($this->once())->method('getScheduledEntityInsertions')
            ->will($this->returnValue(array($entity1, $entity2, $entity3)));

        $uow->expects($this->once())->method('getScheduledEntityUpdates')
            ->will($this->returnValue(array($entity4, $entity5, $entity6)));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('getUnitOfWork'))
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())->method('getUnitOfWork')->will($this->returnValue($uow));

        $this->subscriber->preFlush(new PreFlushEventArgs($em));
    }
}
