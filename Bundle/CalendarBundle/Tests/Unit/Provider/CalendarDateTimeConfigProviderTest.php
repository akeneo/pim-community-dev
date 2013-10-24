<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Provider;

use Oro\Bundle\CalendarBundle\Provider\CalendarDateTimeConfigProvider;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;

class CalendarDateTimeConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    protected $cm;

    /** @var CalendarDateTimeConfigProvider */
    protected $provider;

    protected function setUp()
    {
        $this->cm = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = new CalendarDateTimeConfigProvider($this->cm);
    }

    /**
     * @dataProvider getDateRangeProvider
     */
    public function testGetDateRange($current, $start, $end)
    {
        $this->cm->expects($this->at(0))
            ->method('get')
            ->with('oro_locale.timezone')
            ->will($this->returnValue('America/New_York'));
        $this->cm->expects($this->at(1))
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue('en_US'));

        $date = new \DateTime($current, new \DateTimeZone('UTC'));
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
        $this->cm->expects($this->at(0))
            ->method('get')
            ->with('oro_locale.date_format')
            ->will($this->returnValue('d.m.Y'));
        $this->cm->expects($this->at(1))
            ->method('get')
            ->with('oro_locale.time_format')
            ->will($this->returnValue('H:i'));
        $this->cm->expects($this->at(2))
            ->method('get')
            ->with('oro_locale.timezone')
            ->will($this->returnValue('America/New_York'));
        $this->cm->expects($this->at(3))
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue('en_US'));

        $date = new \DateTime('2014-01-20T10:30:15+00:00', new \DateTimeZone('UTC'));
        $result = $this->provider->getCalendarOptions($date);

        $this->assertEquals('2014-01-20', $result['date']);
        $this->assertEquals(-300, $result['timezoneOffset']);
        $this->assertEquals(0, $result['firstDay']);
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
        $this->cm->expects($this->at(0))
            ->method('get')
            ->with('oro_locale.date_format')
            ->will($this->returnValue('Y-m-d'));
        $this->cm->expects($this->at(1))
            ->method('get')
            ->with('oro_locale.time_format')
            ->will($this->returnValue('g:i a'));
        $this->cm->expects($this->at(2))
            ->method('get')
            ->with('oro_locale.timezone')
            ->will($this->returnValue('America/New_York'));
        $this->cm->expects($this->at(3))
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue('en_US'));

        $date = new \DateTime('2014-01-20T10:30:15+00:00', new \DateTimeZone('UTC'));
        $result = $this->provider->getCalendarOptions($date);

        $this->assertEquals('2014-01-20', $result['date']);
        $this->assertEquals(-300, $result['timezoneOffset']);
        $this->assertEquals(0, $result['firstDay']);
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

    public function testGetFirstDayOfWeek()
    {
        $this->cm->expects($this->once())
            ->method('get')
            ->with('oro_locale.locale')
            ->will($this->returnValue('en_US'));

        $result = ReflectionUtil::callProtectedMethod(
            $this->provider,
            'getFirstDayOfWeek',
            array()
        );
        $this->assertEquals(0, $result);
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
