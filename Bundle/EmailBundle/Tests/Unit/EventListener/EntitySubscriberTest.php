<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\EventListener;

use Oro\Bundle\EmailBundle\EventListener\EntitySubscriber;
use Doctrine\ORM\Events;

class EntitySubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntitySubscriber */
    private $subscriber;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $emailAddressManager;

    protected function setUp()
    {
        $this->emailAddressManager = $this->getMockBuilder('Oro\Bundle\EmailBundle\EventListener\EmailAddressManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriber = new EntitySubscriber($this->emailAddressManager);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(
                Events::loadClassMetadata,
                Events::onFlush,
            ),
            $this->subscriber->getSubscribedEvents()
        );
    }

    public function testLoadClassMetadata()
    {
        $eventArgs = $this->getMockBuilder('Doctrine\ORM\Event\LoadClassMetadataEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailAddressManager->expects($this->once())
            ->method('handleLoadClassMetadata')
            ->with($this->identicalTo($eventArgs));

        $this->subscriber->loadClassMetadata($eventArgs);
    }

    public function testOnFlush()
    {
        $eventArgs = $this->getMockBuilder('Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailAddressManager->expects($this->once())
            ->method('handleOnFlush')
            ->with($this->identicalTo($eventArgs));

        $this->subscriber->onFlush($eventArgs);
    }
}
