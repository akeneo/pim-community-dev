<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Entity;

use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarConnection;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CalendarConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $calendar = new Calendar();
        $obj = new CalendarConnection($calendar);
        $this->assertEquals($calendar, $obj->getConnectedCalendar());
    }

    public function testIdGetter()
    {
        $obj = new CalendarConnection(new Calendar());
        ReflectionUtil::setId($obj, 1);
        $this->assertEquals(1, $obj->getId());
    }

    public function testCreatedAtGetter()
    {
        $obj = new CalendarConnection(new Calendar());
        $date = new \DateTime();
        ReflectionUtil::setCreatedAt($obj, $date);
        $this->assertEquals($date, $obj->getCreatedAt());
    }

    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed  $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new CalendarConnection(new Calendar());

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($obj, $property, $value);
        $this->assertEquals($value, $accessor->getValue($obj, $property));
    }

    public function propertiesDataProvider()
    {
        return array(
            array('calendar', new Calendar()),
            array('connectedCalendar', new Calendar()),
            array('color', 'c0c0c0'),
            array('backgroundColor', 'c0c0c0'),
        );
    }
}
