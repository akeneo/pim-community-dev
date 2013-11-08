<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Provider;

use Oro\Bundle\CalendarBundle\Provider\CalendarDateTimeConfigProvider;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;

class CalendarDateTimeConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $calendar;

    /**
     * @var CalendarDateTimeConfigProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->getMock();

        $this->calendar = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\Calendar')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new CalendarDateTimeConfigProvider($this->localeSettings);
    }

    /**
     * @dataProvider getDateRangeProvider
     */
    public function testGetDateRange($current, $start, $end)
    {
        $this->localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->will($this->returnValue('America/New_York'));

        $this->localeSettings->expects($this->once())
            ->method('getCalendar')
            ->will($this->returnValue($this->calendar));

        $this->calendar->expects($this->once())->method('getFirstDayOfWeek')
            ->will($this->returnValue(1));

        $date   = new \DateTime($current, new \DateTimeZone('UTC'));
        $result = $this->provider->getDateRange($date);

        $this->assertCount(2, $result);
        $this->assertInstanceOf('\DateTime', $result['startDate']);
        $result['startDate']->setTimezone(new \DateTimeZone('UTC'));
        $this->assertEquals($start, $result['startDate']->format('c'));
        $this->assertInstanceOf('\DateTime', $result['endDate']);
        $result['endDate']->setTimezone(new \DateTimeZone('UTC'));
        $this->assertEquals($end, $result['endDate']->format('c'));
    }

    public function getDateRangeProvider()
    {
        return array(
            array('2015-05-01T10:30:15+00:00', '2015-04-26T04:00:00+00:00', '2015-06-07T04:00:00+00:00'),
            array('2015-05-15T10:30:15+00:00', '2015-04-26T04:00:00+00:00', '2015-06-07T04:00:00+00:00'),
            array('2015-05-31T10:30:15+00:00', '2015-04-26T04:00:00+00:00', '2015-06-07T04:00:00+00:00'),
            array('2014-06-01T10:30:15+00:00', '2014-06-01T04:00:00+00:00', '2014-07-13T04:00:00+00:00'),
            array('2014-01-01T10:30:15+00:00', '2013-12-29T05:00:00+00:00', '2014-02-09T05:00:00+00:00'),
            array('2014-01-20T10:30:15+00:00', '2013-12-29T05:00:00+00:00', '2014-02-09T05:00:00+00:00'),
            array('2014-01-31T10:30:15+00:00', '2013-12-29T05:00:00+00:00', '2014-02-09T05:00:00+00:00'),
        );
    }

    public function testGetTimezoneOffset()
    {
        $this->localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->will($this->returnValue('America/New_York'));

        $date = new \DateTime('2014-01-20T10:30:15+00:00', new \DateTimeZone('UTC'));

        $this->assertEquals(-300, $this->provider->getTimezoneOffset($date));
    }
}
