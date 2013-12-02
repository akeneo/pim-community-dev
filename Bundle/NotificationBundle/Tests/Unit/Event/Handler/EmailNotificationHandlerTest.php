<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Event\Handler;

use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationAdapter;
use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler;

class EmailNotificationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test handler
     */
    public function testHandle()
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $event = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Event\NotificationEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $notification = $this->getMock('Oro\Bundle\NotificationBundle\Entity\EmailNotification');
        $notifications = array($notification);
        $notificationsForProcessor = array(new EmailNotificationAdapter($entity, $notification, $em));

        $processor = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $processor->expects($this->once())
            ->method('process')
            ->with($this->identicalTo($entity), $this->equalTo($notificationsForProcessor));

        $handler = new EmailNotificationHandler($processor, $em);
        $handler->handle($event, $notifications);
    }
}
