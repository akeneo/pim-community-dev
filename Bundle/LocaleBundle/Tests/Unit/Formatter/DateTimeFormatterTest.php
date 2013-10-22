<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;

class DateTimeFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var DateTimeFormatter
     */
    protected $formatter;

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formatter = new DateTimeFormatter($this->localeSettings);
    }

    /**
     * @dataProvider formatDataProvider
     */
    public function testFormat(
        $expected,
        $date,
        $dateType,
        $timeType,
        $locale,
        $timeZone,
        $defaultLocale = null,
        $defaultTimeZone = null
    ) {
        $at = 0;
        if ($defaultLocale) {
            $this->localeSettings->expects($this->at($at++))->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        if ($defaultTimeZone) {
            $this->localeSettings->expects($this->at($at++))->method('getTimeZone')
                ->will($this->returnValue($defaultTimeZone));
        }
        $this->assertEquals(
            $expected,
            $this->formatter->format($date, $dateType, $timeType, $locale, $timeZone)
        );
    }

    public function formatDataProvider()
    {
        return array(
            'full_format' => array(
                'expected' => 'Tuesday, December 31, 2013 2:00:00 PM Pacific Standard Time',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'timeType' => \IntlDateFormatter::FULL,
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
            ),
            'string_date' => array(
                'expected' => '14-01-01 12:00 AM',
                'date' => '2014-01-01 00:00:00',
                'dateType' => \IntlDateFormatter::SHORT,
                'timeType' => \IntlDateFormatter::SHORT,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles',
            ),
            'integer_date' => array(
                'expected' => '14-01-01 12:00 AM',
                'date' => 1388563200,
                'dateType' => \IntlDateFormatter::SHORT,
                'timeType' => \IntlDateFormatter::SHORT,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles',
            ),
            'short_format_and_text_date_types' => array(
                'expected' => '12/31/13 2:00 PM',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => 'short',
                'timeType' => 'short',
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
            ),
            'long_date_without_time' => array(
                'expected' => '31 décembre 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::LONG,
                'timeType' => \IntlDateFormatter::NONE,
                'locale' => 'fr_FR',
                'timeZone' => 'America/Los_Angeles'
            ),
            'default_date_and_time_type' => array(
                'expected' => '2013-12-31 2:00:00 PM',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => null,
                'timeType' => null,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles'
            ),
        );
    }

    /**
     * @dataProvider formatDateDataProvider
     */
    public function testFormatDate(
        $expected,
        $date,
        $dateType,
        $locale,
        $timeZone,
        $defaultLocale = null,
        $defaultTimeZone = null
    ) {
        $at = 0;
        if ($defaultLocale) {
            $this->localeSettings->expects($this->at($at++))->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        if ($defaultTimeZone) {
            $this->localeSettings->expects($this->at($at++))->method('getTimeZone')
                ->will($this->returnValue($defaultTimeZone));
        }
        $this->assertEquals(
            $expected,
            $this->formatter->formatDate($date, $dateType, $locale, $timeZone)
        );
    }

    public function formatDateDataProvider()
    {
        return array(
            'full_date' => array(
                'expected' => 'Tuesday, December 31, 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
            ),
            'short_date_and_text_date_type' => array(
                'expected' => '12/31/13',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => 'short',
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
            ),
            'long_date' => array(
                'expected' => '31 décembre 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::LONG,
                'locale' => 'fr_FR',
                'timeZone' => 'America/Los_Angeles'
            ),
            'default_date_type' => array(
                'expected' => '2013-12-31',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => null,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles'
            ),
        );
    }

    /**
     * @dataProvider formatTimeDataProvider
     */
    public function testFormatTime(
        $expected,
        $date,
        $timeType,
        $locale,
        $timeZone,
        $defaultLocale = null,
        $defaultTimeZone = null
    ) {
        $at = 0;
        if ($defaultLocale) {
            $this->localeSettings->expects($this->at($at++))->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        if ($defaultTimeZone) {
            $this->localeSettings->expects($this->at($at++))->method('getTimeZone')
                ->will($this->returnValue($defaultTimeZone));
        }
        $this->assertEquals(
            $expected,
            $this->formatter->formatTime($date, $timeType, $locale, $timeZone)
        );
    }

    public function formatTimeDataProvider()
    {
        return array(
            'full_date' => array(
                'expected' => '2:00:00 PM Pacific Standard Time',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
            ),
            'short_date_and_text_date_type' => array(
                'expected' => '2:00 PM',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => 'short',
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
            ),
            'long_time' => array(
                'expected' => '14:00:00 UTC-08:00',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::LONG,
                'locale' => 'fr_FR',
                'timeZone' => 'America/Los_Angeles'
            ),
            'default_date_type' => array(
                'expected' => '2:00:00 PM',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => null,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles'
            ),
        );
    }

    /**
     * @param string $date
     * @param string $timeZone
     * @return \DateTime
     */
    protected function createDateTime($date, $timeZone)
    {
        $dateType = new \DateTime($date);

        $dateType->setTimezone(new \DateTimeZone($timeZone));

        return $dateType;
    }

    /**
     * @dataProvider getDatePatternDataProvider
     */
    public function testGetDatePattern($dateType, $timeType, $locale, $expected)
    {
        $this->assertEquals($expected, $this->formatter->getPattern($dateType, $timeType, $locale));
    }

    public function getDatePatternDataProvider()
    {
        return array(
            array(\IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'en_US', 'EEEE, MMMM d, y h:mm:ss a zzzz'),
            array(\IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'ru_RU', 'EEEE, d MMMM y \'г\'. H:mm:ss zzzz'),
            array(\IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'fr_FR', 'EEEE d MMMM y HH:mm:ss zzzz'),
            array('full', 'full', 'fr_FR', 'EEEE d MMMM y HH:mm:ss zzzz'),
        );
    }
}
