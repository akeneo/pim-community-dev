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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTimeFormatter;

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

        $this->dateTimeFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new CalendarDateTimeConfigProvider($this->localeSettings, $this->dateTimeFormatter);
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

    public function testGetCalendarOptions1()
    {
        $this->localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->will($this->returnValue('America/New_York'));

        $this->localeSettings->expects($this->once())
            ->method('getCalendar')
            ->will($this->returnValue($this->calendar));

        $this->calendar->expects($this->at(0))->method('getFirstDayOfWeek')
            ->will($this->returnValue(1));

        $this->calendar->expects($this->at(1))->method('getMonthNames')
            ->with('wide')
            ->will(
                $this->returnValue(
                    array(
                        1 => 'January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'
                    )
                )
            );

        $this->calendar->expects($this->at(2))->method('getMonthNames')
            ->with('abbreviated')
            ->will(
                $this->returnValue(
                    array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec')
                )
            );

        $this->calendar->expects($this->at(3))->method('getDayOfWeekNames')
            ->with('wide')
            ->will(
                $this->returnValue(
                    array(1 => 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')
                )
            );

        $this->calendar->expects($this->at(4))->method('getDayOfWeekNames')
            ->with('abbreviated')
            ->will(
            $this->returnValue(
                array(1 => 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat')
            )
        );

        $this->dateTimeFormatter->expects($this->at(0))->method('getPattern')
            ->with('medium', 'none')
            ->will($this->returnValue('d.m.Y'));

        $this->dateTimeFormatter->expects($this->at(1))->method('getPattern')
            ->with('none', 'short')
            ->will($this->returnValue('H:i'));

        $date   = new \DateTime('2014-01-20T10:30:15+00:00', new \DateTimeZone('UTC'));
        $result = $this->provider->getCalendarOptions($date);

        $this->assertEquals('2014-01-20', $result['date']);
        $this->assertEquals(-300, $result['timezoneOffset']);
        $this->assertEquals(0, $result['firstDay']);
        $this->assertEquals(
            array(
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ),
            $result['monthNames']
        );
        $this->assertEquals(
            array(
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec'
            ),
            $result['monthNamesShort']
        );
        $this->assertEquals(
            array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            $result['dayNames']
        );
        $this->assertEquals(
            array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
            $result['dayNamesShort']
        );
        $this->assertEquals('MMMM yyyy', $result['titleFormat']['month']);
        $this->assertEquals('d[ MMMM][ yyyy]{ \'&#8212;\' d MMMM yyyy}', $result['titleFormat']['week']);
        $this->assertEquals('dddd, dd.MM.yyyy', $result['titleFormat']['day']);
        $this->assertEquals('ddd', $result['columnFormat']['month']);
        $this->assertEquals('ddd dd.MM', $result['columnFormat']['week']);
        $this->assertEquals('dddd dd.MM', $result['columnFormat']['day']);
        $this->assertEquals('HH:mm', $result['timeFormat']['']);
        $this->assertEquals('HH:mm{ - HH:mm}', $result['timeFormat']['agenda']);
        $this->assertEquals('HH:mm', $result['axisFormat']);
    }

    public function testGetCalendarOptions2()
    {
        $this->localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->will($this->returnValue('America/New_York'));

        $this->localeSettings->expects($this->once())
            ->method('getCalendar')
            ->will($this->returnValue($this->calendar));

        $this->calendar->expects($this->at(0))->method('getFirstDayOfWeek')
            ->will($this->returnValue(1));

        $this->calendar->expects($this->at(1))->method('getMonthNames')
            ->with('wide')
            ->will(
            $this->returnValue(
                array(
                    1 => 'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                )
            )
        );

        $this->calendar->expects($this->at(2))->method('getMonthNames')
            ->with('abbreviated')
            ->will(
            $this->returnValue(
                array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec')
            )
        );

        $this->calendar->expects($this->at(3))->method('getDayOfWeekNames')
            ->with('wide')
            ->will(
            $this->returnValue(
                array(1 => 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')
            )
        );

        $this->calendar->expects($this->at(4))->method('getDayOfWeekNames')
            ->with('abbreviated')
            ->will(
            $this->returnValue(
                array(1 => 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat')
            )
        );

        $this->dateTimeFormatter->expects($this->at(0))->method('getPattern')
            ->with('medium', 'none')
            ->will($this->returnValue('Y-m-d'));

        $this->dateTimeFormatter->expects($this->at(1))->method('getPattern')
            ->with('none', 'short')
            ->will($this->returnValue('g:i a'));

        $date   = new \DateTime('2014-01-20T10:30:15+00:00', new \DateTimeZone('UTC'));
        $result = $this->provider->getCalendarOptions($date);

        $this->assertEquals('2014-01-20', $result['date']);
        $this->assertEquals(-300, $result['timezoneOffset']);
        $this->assertEquals(0, $result['firstDay']);
        $this->assertEquals(
            array(
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ),
            $result['monthNames']
        );
        $this->assertEquals(
            array(
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec'
            ),
            $result['monthNamesShort']
        );
        $this->assertEquals(
            array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            $result['dayNames']
        );
        $this->assertEquals(
            array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
            $result['dayNamesShort']
        );
        $this->assertEquals('MMMM yyyy', $result['titleFormat']['month']);
        $this->assertEquals('MMMM d[ yyyy]{ \'&#8212;\'[ MMMM] d yyyy}', $result['titleFormat']['week']);
        $this->assertEquals('dddd, yyyy-MM-dd', $result['titleFormat']['day']);
        $this->assertEquals('ddd', $result['columnFormat']['month']);
        $this->assertEquals('ddd MM-dd', $result['columnFormat']['week']);
        $this->assertEquals('dddd MM-dd', $result['columnFormat']['day']);
        $this->assertEquals('h:mm tt', $result['timeFormat']['']);
        $this->assertEquals('h:mm tt{ - h:mm tt}', $result['timeFormat']['agenda']);
        $this->assertEquals('h:mm tt', $result['axisFormat']);
    }

    /**
     * @dataProvider convertToFullCalendarDateFormatProvider
     */
    public function testConvertToFullCalendarDateFormat($src, $expected)
    {
        $result = ReflectionUtil::callProtectedMethod(
            $this->provider,
            'convertToFullCalendarDateFormat',
            array($src)
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider convertToFullCalendarTimeFormatProvider
     */
    public function testConvertToFullCalendarTimeFormat($src, $expected)
    {
        $result = ReflectionUtil::callProtectedMethod(
            $this->provider,
            'convertToFullCalendarTimeFormat',
            array($src)
        );
        $this->assertEquals($expected, $result);
    }

    public function convertToFullCalendarDateFormatProvider()
    {
        return array(
            array('d M Y', 'dd MMM yyyy'),
            array('m/d/y', 'MM/dd/yy'),
            array('d.m.Y', 'dd.MM.yyyy'),
            array('d.m.y', 'dd.MM.yy'),
            array('j.n.y', 'd.M.yy'),
            array('d/m/y', 'dd/MM/yy'),
            array('y/m/d', 'yy/MM/dd'),
            array('Y-m-d', 'yyyy-MM-dd'),
        );
    }

    public function convertToFullCalendarTimeFormatProvider()
    {
        return array(
            array('H:i', 'HH:mm'),
            array('G:i', 'H:mm'),
            array('h:i a', 'hh:mm tt'),
            array('g:i a', 'h:mm tt'),
            array('h:i A', 'hh:mm TT'),
            array('g:i A', 'h:mm TT'),
        );
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
}
