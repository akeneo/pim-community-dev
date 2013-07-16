<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Event;

use Oro\Bundle\NotificationBundle\Event\NotificationEvent;

class NotificationEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \stdClass
     */
    protected $entity;

    /**
     * @var NotificationEvent
     */
    protected $event;

    public function setUp()
    {
        $this->entity = new \stdClass();
        $this->event = new NotificationEvent('test.name.from.construct', $this->entity);
    }

    public function testGetEntity()
    {
        $this->assertEquals($this->entity, $this->event->getEntity());
    }
}
