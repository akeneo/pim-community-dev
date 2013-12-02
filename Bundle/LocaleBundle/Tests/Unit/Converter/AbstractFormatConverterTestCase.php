<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Converter;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterInterface;

abstract class AbstractFormatConverterTestCase extends \PHPUnit_Framework_TestCase
{
    const LOCALE_EN = 'en';
    const LOCALE_RU = 'ru';

    /**
     * @var DateTimeFormatConverterInterface
     */
    protected $converter;

    /**
     * @var LocaleSettings
     */
    protected $formatter;

    /**
     * @var array
     */
    protected $localFormatMap = array(
        array(null,                       null,                       self::LOCALE_EN, "MMM d, y h:mm a"),
        array(\IntlDateFormatter::LONG,   \IntlDateFormatter::MEDIUM, self::LOCALE_EN, "MMMM d, y h:mm:ss a"),
        array(\IntlDateFormatter::LONG,   \IntlDateFormatter::NONE,   self::LOCALE_EN, "MMMM d, y"),
        array(\IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT,  self::LOCALE_EN, "MMM d, y h:mm a"),
        array(\IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE,   self::LOCALE_EN, "MMM d, y"),
        array(null,                       \IntlDateFormatter::NONE,   self::LOCALE_EN, "MMM d, y"),
        array(\IntlDateFormatter::NONE,   \IntlDateFormatter::MEDIUM, self::LOCALE_EN, "h:mm:ss a"),
        array(\IntlDateFormatter::NONE,   \IntlDateFormatter::SHORT,  self::LOCALE_EN, "h:mm a"),
        array(\IntlDateFormatter::NONE,   null,                       self::LOCALE_EN, "h:mm a"),

        array(null,                       null,                       self::LOCALE_RU, "dd.MM.yyyy H:mm"),
        array(\IntlDateFormatter::LONG,   \IntlDateFormatter::MEDIUM, self::LOCALE_RU, "d MMMM y 'г.' H:mm:ss"),
        array(\IntlDateFormatter::LONG,   \IntlDateFormatter::NONE,   self::LOCALE_RU, "d MMMM y 'г.'"),
        array(\IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT,  self::LOCALE_RU, "dd.MM.yyyy H:mm"),
        array(\IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE,   self::LOCALE_RU, "dd.MM.yyyy"),
        array(null,                       \IntlDateFormatter::NONE,   self::LOCALE_RU, "dd.MM.yyyy"),
        array(\IntlDateFormatter::NONE,   \IntlDateFormatter::MEDIUM, self::LOCALE_RU, "H:mm:ss"),
        array(\IntlDateFormatter::NONE,   \IntlDateFormatter::SHORT,  self::LOCALE_RU, "H:mm"),
        array(\IntlDateFormatter::NONE,   null,                       self::LOCALE_RU, "H:mm"),
    );

    protected function setUp()
    {
        $this->formatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter')
            ->disableOriginalConstructor()
            ->setMethods(array('getPattern'))
            ->getMock();

        $this->formatter->expects($this->any())
            ->method('getPattern')
            ->will($this->returnValueMap($this->localFormatMap));

        $this->converter = $this->createFormatConverter();
    }

    protected function tearDown()
    {
        unset($this->formatter);
        unset($this->converter);
    }

    /**
     * @return DateTimeFormatConverterInterface
     */
    abstract protected function createFormatConverter();

    /**
     * @param string $expected
     * @param int $dateFormat
     * @param string $locale
     * @dataProvider getDateFormatDataProvider
     */
    public function testGetDateFormat($expected, $dateFormat, $locale)
    {
        $this->assertEquals($expected, $this->converter->getDateFormat($dateFormat, $locale));
    }

    /**
     * @return array
     */
    abstract public function getDateFormatDataProvider();

    /**
     * @param string $expected
     * @param int $timeFormat
     * @param string $locale
     * @dataProvider getTimeFormatDataProvider
     */
    public function testGetTimeFormat($expected, $timeFormat, $locale)
    {
        $this->assertEquals($expected, $this->converter->getTimeFormat($timeFormat, $locale));
    }

    /**
     * @return array
     */
    abstract public function getTimeFormatDataProvider();

    /**
     * @param string $expected
     * @param int $dateFormat
     * @param int $timeFormat
     * @param string $locale
     * @dataProvider getDateTimeFormatDataProvider
     */
    public function testGetDateTimeFormat($expected, $dateFormat, $timeFormat, $locale)
    {
        $this->assertEquals($expected, $this->converter->getDateTimeFormat($dateFormat, $timeFormat, $locale));
    }

    /**
     * @return array
     */
    abstract public function getDateTimeFormatDataProvider();
}
