<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler;
use Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface;
use Oro\Bundle\NotificationBundle\Provider\NotificationManager;

class NotificationManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NotificationManager
     */
    protected $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var EventHandlerInterface
     */
    protected $handler;

    public function setUp()
    {
        $this->em = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->className = 'Oro\Bundle\NotificationBundle\Entity\EmailNotification';
        $this->manager = new NotificationManager($this->em, $this->className);
        $this->handler = $this->getMockBuilder('Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->addHandler($this->handler);

        $this->assertCount(1, $this->manager->getHandlers());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testProcess($eventPropagationStopped)
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\ContainAuthorInterface');
        $event = $this->getMock('Oro\Bundle\NotificationBundle\Event\NotificationEvent', array(), array($entity));
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));
        $eventName = 'namespace.event_name';
        $event->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($eventName));

        $notificationRules = array(
            new \stdClass()
        );

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($event, $notificationRules);

        $event->expects($this->once())
            ->method('isPropagationStopped')
            ->will($this->returnValue($eventPropagationStopped));

        $repository = $this->getMockBuilder(
            'Oro\Bundle\NotificationBundle\Entity\Repository\EmailNotificationRepository'
        )->disableOriginalConstructor()->getMock();
        $repository->expects($this->once())
            ->method('getRulesByCriteria')
            ->with(get_class($entity), $eventName)
            ->will($this->returnValue($notificationRules));

        $this->em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($this->className))
            ->will($this->returnValue($repository));

        $this->manager->process($event);
    }

    public function dataProvider()
    {
        return array(
            array(false),
            array(true),
        );
    }

    /**
     * Test setters, getters
     */
    public function testAddAndGetHandlers()
    {
        $handler = $this->getMock('Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface');
        $this->manager->addHandler($handler);

        $this->assertCount(2, $this->manager->getHandlers());
        $this->assertContains($handler, $this->manager->getHandlers());
    }
}
