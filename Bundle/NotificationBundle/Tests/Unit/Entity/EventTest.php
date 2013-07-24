<?php

namespace Oro\Bundle\NotificationBundle\Tests\Unit\Entity;

use Oro\Bundle\NotificationBundle\Entity\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Event
     */
    protected $event;

    public function setUp()
    {
        $this->event = new Event('test.name.from.construct');

        // get id should return null cause this entity was not loaded from DB
        $this->assertNull($this->event->getId());
    }

    public function tearDown()
    {
        unset($this->event);
    }

    public function testSetterGetterForName()
    {
        $this->assertEquals('test.name.from.construct', $this->event->getName());
        $this->event->setName('test.new.name');
        $this->assertEquals('test.new.name', $this->event->getName());
    }

    public function testSetterGetterForDescription()
    {
        // empty from construct
        $this->assertNull($this->event->getDescription());
        $this->event->setDescription('description');
        $this->assertEquals('description', $this->event->getDescription());

        // set description from construct
        $newInstance = new Event('test.name', 'test.description');
        $this->assertEquals('test.description', $newInstance->getDescription());
    }
}
