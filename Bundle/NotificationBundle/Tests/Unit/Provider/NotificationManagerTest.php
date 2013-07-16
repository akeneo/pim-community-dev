<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Provider;

use Oro\Bundle\NotificationBundle\Provider\NotificationManager;

class NotificationManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NotificationManager
     */
    protected $manager;

    public function setUp()
    {
        $this->manager = new NotificationManager();

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
