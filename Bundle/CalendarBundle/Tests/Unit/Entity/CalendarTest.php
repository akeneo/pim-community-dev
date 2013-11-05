<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Entity;

use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarConnection;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    public function testIdGetter()
    {
        $obj = new Calendar();
        ReflectionUtil::setId($obj, 1);
        $this->assertEquals(1, $obj->getId());
    }

    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed  $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new Calendar();

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($obj, $property, $value);
        $this->assertEquals($value, $accessor->getValue($obj, $property));
    }

    public function testConnections()
    {
        $obj = new Calendar();
        $this->assertCount(0, $obj->getConnections());
        $connection = new CalendarConnection(new Calendar());
        $obj->addConnection($connection);
        $this->assertCount(1, $obj->getConnections());
        $connections = $obj->getConnections();
        $this->assertTrue($connection === $connections[0]);
        $obj->removeConnection($connection);
        $this->assertCount(0, $obj->getConnections());
    }

    /**
     * @expectedException \Oro\Bundle\EntityBundle\Exception\InvalidEntityException
     */
    public function testAddConnectionShouldNotAllowReconnect()
    {
        $obj = new Calendar();
        $connection = new CalendarConnection(new Calendar());
        $connection->setCalendar(new Calendar());
        $obj->addConnection($connection);
    }

    /**
     * @expectedException \Oro\Bundle\EntityBundle\Exception\InvalidEntityException
     */
    public function testAddConnectionShouldNotAllowConnectEmpty()
    {
        $obj = new Calendar();
        $connection = new CalendarConnection(new Calendar());
        ReflectionUtil::setPrivateProperty($connection, 'connectedCalendar', null);
        $obj->addConnection($connection);
    }

    public function testEvents()
    {
        $obj = new Calendar();
        $this->assertCount(0, $obj->getConnections());
        $event = new CalendarEvent();
        $obj->addEvent($event);
        $this->assertCount(1, $obj->getEvents());
        $events = $obj->getEvents();
        $this->assertTrue($event === $events[0]);
        $this->assertTrue($obj === $events[0]->getCalendar());
    }

    public function propertiesDataProvider()
    {
        return array(
            array('name', 'testName'),
            array('owner', new User())
        );
    }
}
