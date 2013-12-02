<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Twig;

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

        $this->assertCount(3, $filters);

        $this->assertInstanceOf('Twig_SimpleFilter', $filters[0]);
        $this->assertEquals('oro_format_datetime', $filters[0]->getName());

        $this->assertInstanceOf('Twig_SimpleFilter', $filters[1]);
        $this->assertEquals('oro_format_date', $filters[1]->getName());

        $this->assertInstanceOf('Twig_SimpleFilter', $filters[2]);
        $this->assertEquals('oro_format_time', $filters[2]->getName());
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

    public function testGetName()
    {
        $this->assertEquals('oro_locale_datetime', $this->extension->getName());
    }
}
