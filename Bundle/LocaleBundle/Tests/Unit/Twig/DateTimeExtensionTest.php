<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Model;

use Oro\Bundle\LocaleBundle\Twig\DateTimeExtension;

class DateTimeExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DateTimeExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new DateTimeExtension($this->formatter);
    }

    public function testGetFilters()
    {
        $filters = $this->extension->getFilters();
        $this->assertArrayContainsHasFilter('oro_format_datetime', 'formatDateTime', $filters);
        $this->assertArrayContainsHasFilter('oro_format_date', 'formatDate', $filters);
        $this->assertArrayContainsHasFilter('oro_format_time', 'formatTime', $filters);
    }

    protected function assertArrayContainsHasFilter($filterName, $extensionMethod, array $filters)
    {
        $this->assertArrayHasKey($filterName, $filters);
        /** @var \Twig_Filter_Method $filter */
        $filter = $filters[$filterName];
        $this->assertInstanceOf('Twig_Filter_Method', $filter);
        $this->assertAttributeEquals($extensionMethod, 'method', $filter);
    }

    public function testFormatDateTime()
    {
        $value = new \DateTime('2013-12-31 00:00:00');
        $dateType = 'short';
        $timeType = 'short';
        $locale = 'en_US';
        $timeZone = 'America/Los_Angeles';
        $options = array(
            'dateType' => $dateType,
            'timeType' => $timeType,
            'locale' => $locale,
            'timeZone' => $timeZone
        );
        $expectedResult = '12/31/13 12:00 AM';

        $this->formatter->expects($this->once())->method('format')
            ->with($value, $dateType, $timeType, $locale, $timeZone)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->formatDateTime($value, $options));
    }

    public function testFormatDate()
    {
        $value = new \DateTime('2013-12-31 00:00:00');
        $dateType = 'short';
        $locale = 'en_US';
        $timeZone = 'America/Los_Angeles';
        $options = array(
            'dateType' => $dateType,
            'locale' => $locale,
            'timeZone' => $timeZone
        );
        $expectedResult = '12/31/13';

        $this->formatter->expects($this->once())->method('formatDate')
            ->with($value, $dateType, $locale, $timeZone)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->formatDate($value, $options));
    }

    public function testFormatTime()
    {
        $value = new \DateTime('2013-12-31 00:00:00');
        $timeType = 'short';
        $locale = 'en_US';
        $timeZone = 'America/Los_Angeles';
        $options = array(
            'timeType' => $timeType,
            'locale' => $locale,
            'timeZone' => $timeZone
        );
        $expectedResult = '12 AM';

        $this->formatter->expects($this->once())->method('formatTime')
            ->with($value, $timeType, $locale, $timeZone)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->formatTime($value, $options));
    }
}
