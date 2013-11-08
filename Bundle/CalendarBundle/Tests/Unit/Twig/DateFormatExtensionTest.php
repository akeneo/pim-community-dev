<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Twig;

use Oro\Bundle\CalendarBundle\Twig\DateFormatExtension;

class DateFormatExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $formatter;

    /** @var DateFormatExtension */
    protected $extension;

    protected function setUp()
    {
        $this->formatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formatter->expects($this->any())
            ->method('format')
            ->will($this->returnValue('DateTime'));
        $this->formatter->expects($this->any())
            ->method('formatDate')
            ->will($this->returnValue('Date'));
        $this->formatter->expects($this->any())
            ->method('formatTime')
            ->will($this->returnValue('Time'));

        $this->extension = new DateFormatExtension($this->formatter);
    }

    /**
     * @dataProvider formatCalendarDateRangeProvider
     */
    public function testFormatCalendarDateRange($start, $end, $skipTime, $expected)
    {
        $startDate = new \DateTime($start);
        $endDate = $end === null ? null : new \DateTime($end);

        $result = $this->extension->formatCalendarDateRange($startDate, $endDate, $skipTime);

        $this->assertEquals($expected, $result);
    }

    public function formatCalendarDateRangeProvider()
    {
        return array(
            array('2010-05-01T10:30:15+00:00', null, false, 'DateTime'),
            array('2010-05-01T10:30:15+00:00', null, true, 'Date'),
            array('2010-05-01T10:30:15+00:00', '2010-05-01T10:30:15+00:00', false, 'DateTime'),
            array('2010-05-01T10:30:15+00:00', '2010-05-01T10:30:15+00:00', true, 'Date'),
            array('2010-05-01T10:30:15+00:00', '2010-05-01T11:30:15+00:00', false, 'Date Time - Time'),
            array('2010-05-01T10:30:15+00:00', '2010-05-01T11:30:15+00:00', true, 'Date'),
            array('2010-05-01T10:30:15+00:00', '2010-05-02T10:30:15+00:00', false, 'DateTime - DateTime'),
            array('2010-05-01T10:30:15+00:00', '2010-05-02T10:30:15+00:00', true, 'Date - Date'),
        );
    }
}
