<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

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

    public function setUp()
    {
        $this->em = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $className = 'Oro\Bundle\NotificationBundle\Entity\EmailNotification';
        $this->manager = new NotificationManager($this->em, $className);

        $this->assertEmpty($this->manager->getHandlers());
    }

    public function testAddAndGetHandlers()
    {
        $handler = $this->getMock('Oro\Bundle\NotificationBundle\Event\Handler\EventHandlerInterface');
        $this->manager->addHandler($handler);

        $this->assertCount(1, $this->manager->getHandlers());
        $this->assertContains($handler, $this->manager->getHandlers());
    }
}
