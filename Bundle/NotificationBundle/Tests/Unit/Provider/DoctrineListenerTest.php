<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

use Oro\Bundle\NotificationBundle\Provider\DoctrineListener;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Oro\Bundle\NotificationBundle\Provider\EventNamesExtractor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DoctrineListenerTest extends TestCase
{
    /**
     * @var DoctrineListener
     */
    protected $listener;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function setUp()
    {
        $this->listener = new DoctrineListener();
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->listener->setEventDispatcher($this->eventDispatcher);

        $this->assertEquals($this->eventDispatcher, $this->listener->getEventDispatcher());
    }

    /**
     * @dataProvider eventData
     * @param $methodName
     * @param $eventName
     */
    public function testEventDispatchers($methodName, $eventName)
    {
        $args = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $args->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue('something'));

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo($eventName), $this->isInstanceOf('Symfony\Component\EventDispatcher\Event'));

        $this->listener->$methodName($args);
    }

    /**
     * data provider
     */
    public function eventData()
    {
        return array(
            array('postUpdate',  'oro.event.entity.post_update'),
            array('postPersist', 'oro.event.entity.post_persist'),
            array('postRemove',  'oro.event.entity.post_remove'),
        );
    }
}
