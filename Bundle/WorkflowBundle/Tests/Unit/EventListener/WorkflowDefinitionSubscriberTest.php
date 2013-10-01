<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\EventListener\WorkflowDefinitionSubscriber;

class WorkflowDefinitionSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowDefinitionSubscriber
     */
    protected $subscriber;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $binder;

    protected function setUp()
    {
        $this->subscriber = new WorkflowDefinitionSubscriber();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array('prePersist'), $this->subscriber->getSubscribedEvents());
    }

    public function testPrePersist()
    {
        $entity = new WorkflowItem();
        $entity->setWorkflowName('test_workflow');

        $workflowDefinition = new WorkflowDefinition();
        $workflowDefinition->setName('test_workflow');

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('find'))
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())->method('find')
            ->with('OroWorkflowBundle:WorkflowDefinition', 'test_workflow')
            ->will($this->returnValue($workflowDefinition));

        $this->subscriber->prePersist(new LifecycleEventArgs($entity, $em));

        $this->assertSame($workflowDefinition, $entity->getDefinition());
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @expectedExceptionMessage Cannot find workflow definition "test_workflow"
     */
    public function testPrePersistFails()
    {
        $entity = new WorkflowItem();
        $entity->setWorkflowName('test_workflow');

        $workflowDefinition = new WorkflowDefinition();
        $workflowDefinition->setName('test_workflow');

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('find'))
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())->method('find')
            ->with('OroWorkflowBundle:WorkflowDefinition', 'test_workflow')
            ->will($this->returnValue(null));

        $this->subscriber->prePersist(new LifecycleEventArgs($entity, $em));
    }

    public function testPrePersistSkips()
    {
        $entity = $this->getMock('FooEntity');

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->never())->method($this->anything());

        $this->subscriber->prePersist(new LifecycleEventArgs($entity, $em));
    }
}
