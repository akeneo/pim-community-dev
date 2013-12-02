<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Notification;

use Oro\Bundle\CalendarBundle\Notification\RemindTimeCalculator;

class RemindTimeCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testCalculateRemindAt()
    {
        $calculator = new RemindTimeCalculator(15);
        $date = new \DateTime();
        $expected = clone $date;
        $expected->sub(new \DateInterval('PT15M'));

        $this->assertEquals($expected, $calculator->calculateRemindAt($date));
    }
}
