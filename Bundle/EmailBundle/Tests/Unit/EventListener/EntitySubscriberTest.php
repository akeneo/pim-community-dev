<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\EventListener;

use Oro\Bundle\EmailBundle\EventListener\EntitySubscriber;
use Doctrine\ORM\Events;

class EntitySubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntitySubscriber */
    private $subscriber;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $emailOwnerManager;

    protected function setUp()
    {
        $this->emailOwnerManager = $this->getMockBuilder('Oro\Bundle\EmailBundle\Entity\Manager\EmailOwnerManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriber = new EntitySubscriber($this->emailOwnerManager);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(
                Events::onFlush,
            ),
            $this->subscriber->getSubscribedEvents()
        );
    }

    public function testOnFlush()
    {
        $eventArgs = $this->getMockBuilder('Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailOwnerManager->expects($this->once())
            ->method('handleOnFlush')
            ->with($this->identicalTo($eventArgs));

        $this->subscriber->onFlush($eventArgs);
    }
}
