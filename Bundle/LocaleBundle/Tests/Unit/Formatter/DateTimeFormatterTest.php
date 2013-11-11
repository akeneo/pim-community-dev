<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\LocaleBundle\Tests\Unit\IcuAwareTestCase;

class DateTimeFormatterTest extends IcuAwareTestCase
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
        $this->ignoreIfIcuVersionGreaterThan('4.8.1.1');
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
        $language,
        $defaultLocale = null,
        $defaultTimeZone = null
    ) {
        $this->localeSettings->expects($this->once())->method('getLanguage')->will($this->returnValue($language));
        $methodCalls = 1;
        if ($defaultLocale) {
            $methodCalls++;
            $this->localeSettings->expects($this->once())->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        if ($defaultTimeZone) {
            $methodCalls++;
            $this->localeSettings->expects($this->once())->method('getTimeZone')
                ->will($this->returnValue($defaultTimeZone));
        }
        $this->localeSettings->expects($this->exactly($methodCalls))->method($this->anything());

        $this->assertEquals(
            $expected,
            $this->formatter->format($date, $dateType, $timeType, $locale, $timeZone)
        );
    }

    public function formatDataProvider()
    {
        return array(
            'full_format' => array(
                'expected' => 'Tuesday, December 31, 2013 4:00:00 PM Pacific Standard Time',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'timeType' => \IntlDateFormatter::FULL,
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_US',
            ),
            'full_format_default_locale_and_timezone' => array(
                'expected' => 'Tuesday, December 31, 2013 4:00:00 PM Pacific Standard Time',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'timeType' => \IntlDateFormatter::FULL,
                'locale' => null,
                'timeZone' => null,
                'language' => 'en_US',
                'defaultLocale' => 'en_US',
                'defaultTimeZone' => 'America/Los_Angeles',
            ),
            'full_format_english_locale_french_language' => array(
                'expected' => 'mardi, décembre 31, 2013 4:00:00 PM heure normale du Pacifique',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'timeType' => \IntlDateFormatter::FULL,
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'fr_FR',
            ),
            'string_date' => array(
                'expected' => '14-01-01 2:00 AM',
                'date' => '2014-01-01 00:00:00',
                'dateType' => \IntlDateFormatter::SHORT,
                'timeType' => \IntlDateFormatter::SHORT,
                'locale' => 'en_CA',
                'timeZone' => 'Europe/Athens',
                'language' => 'en_CA',
            ),
            'string_date_with_timezone' => array(
                'expected' => '14-01-01 12:00 AM',
                'date' => '2014-01-01 00:00:00+2',
                'dateType' => \IntlDateFormatter::SHORT,
                'timeType' => \IntlDateFormatter::SHORT,
                'locale' => 'en_CA',
                'timeZone' => 'Europe/Athens',
                'language' => 'en_CA',
            ),
            'integer_date' => array(
                'expected' => '14-01-01 12:00 AM',
                'date' => 1388563200,
                'dateType' => \IntlDateFormatter::SHORT,
                'timeType' => \IntlDateFormatter::SHORT,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_CA',
            ),
            'short_format_and_text_date_types' => array(
                'expected' => '12/31/13 4:00 PM',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => 'short',
                'timeType' => 'short',
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_US',
            ),
            'long_date_without_time' => array(
                'expected' => '31 décembre 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::LONG,
                'timeType' => \IntlDateFormatter::NONE,
                'locale' => 'fr_FR',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'fr_FR',
            ),
            'long_date_without_time_french_locale_russian_language' => array(
                'expected' => '31 декабря 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::LONG,
                'timeType' => \IntlDateFormatter::NONE,
                'locale' => 'fr_FR',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'ru_RU',
            ),
            'default_date_and_time_type' => array(
                'expected' => '2013-12-31 4:00 PM',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => null,
                'timeType' => null,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_CA',
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
        $language,
        $defaultLocale = null,
        $defaultTimeZone = null
    ) {
        $this->localeSettings->expects($this->once())->method('getLanguage')->will($this->returnValue($language));
        $methodCalls = 1;
        if ($defaultLocale) {
            $this->localeSettings->expects($this->once())->method('getLocale')
                ->will($this->returnValue($defaultLocale));
            $methodCalls++;
        }
        if ($defaultTimeZone) {
            $this->localeSettings->expects($this->once())->method('getTimeZone')
                ->will($this->returnValue($defaultTimeZone));
            $methodCalls++;
        }
        $this->localeSettings->expects($this->exactly($methodCalls))->method($this->anything());

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
                'language' => 'en_US',
            ),
            'full_date_default_locale_and_timezone' => array(
                'expected' => 'Tuesday, December 31, 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'locale' => null,
                'timeZone' => null,
                'language' => 'en_US',
                'defaultLocale' => 'en_US',
                'defaultTimeZone' => 'America/Los_Angeles',
            ),
            'full_date_object' => array(
                'expected' => 'Tuesday, December 31, 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_US',
            ),
            'short_date_and_text_date_type' => array(
                'expected' => '12/31/13',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => 'short',
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_US',
            ),
            'long_date' => array(
                'expected' => '31 décembre 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::LONG,
                'locale' => 'fr_FR',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'fr_FR',
            ),
            'long_date_french_locale_english_language' => array(
                'expected' => '31 December 2013',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::LONG,
                'locale' => 'fr_FR',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en',
            ),
            'default_date_type' => array(
                'expected' => '2013-12-31',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => null,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_CA',
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
        $language,
        $defaultLocale = null,
        $defaultTimeZone = null
    ) {
        $this->localeSettings->expects($this->once())->method('getLanguage')->will($this->returnValue($language));
        $methodCalls = 1;
        if ($defaultLocale) {
            $this->localeSettings->expects($this->once())->method('getLocale')
                ->will($this->returnValue($defaultLocale));
            $methodCalls++;
        }
        if ($defaultTimeZone) {
            $this->localeSettings->expects($this->once())->method('getTimeZone')
                ->will($this->returnValue($defaultTimeZone));
            $methodCalls++;
        }
        $this->localeSettings->expects($this->exactly($methodCalls))->method($this->anything());

        $this->assertEquals(
            $expected,
            $this->formatter->formatTime($date, $timeType, $locale, $timeZone)
        );
    }

    public function formatTimeDataProvider()
    {
        return array(
            'full_date' => array(
                'expected' => '4:00:00 PM Pacific Standard Time',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_US',
            ),
            'full_date_default_locale_and_timezone' => array(
                'expected' => '4:00:00 PM Pacific Standard Time',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'locale' => null,
                'timeZone' => null,
                'language' => 'en_US',
                'defaultLocale' => 'en_US',
                'defaultTimeZone' => 'America/Los_Angeles',
            ),
            'full_date_english_locale_french_language' => array(
                'expected' => '4:00:00 PM heure normale du Pacifique',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::FULL,
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'fr',
            ),
            'short_date_and_text_date_type' => array(
                'expected' => '4:00 PM',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => 'short',
                'locale' => 'en_US',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_US',
            ),
            'long_time' => array(
                'expected' => '16:00:00 UTC-08:00',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => \IntlDateFormatter::LONG,
                'locale' => 'fr_FR',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'fr_FR',
            ),
            'default_date_type' => array(
                'expected' => '4:00 PM',
                'date' => $this->createDateTime('2014-01-01 00:00:00', 'Europe/London'),
                'dateType' => null,
                'locale' => 'en_CA',
                'timeZone' => 'America/Los_Angeles',
                'language' => 'en_CA',
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
        return new \DateTime($date, new \DateTimeZone($timeZone));
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
            array(\IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'fr_FR', 'EEEE d MMMM y HH:mm:ss zzzz'),
            array('full', 'full', 'fr_FR', 'EEEE d MMMM y HH:mm:ss zzzz'),
        );
    }
}
