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
    protected $localeSettings;

    /**
     * @var array
     */
    protected $localFormatMap = array(
        array(self::LOCALE_EN, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT,  "MMM d, y h:mm a"),
        array(self::LOCALE_EN, \IntlDateFormatter::LONG,   \IntlDateFormatter::MEDIUM, "MMMM d, y h:mm:ss a"),
        array(self::LOCALE_EN, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE,   "MMM d, y"),
        array(self::LOCALE_EN, \IntlDateFormatter::LONG,   \IntlDateFormatter::NONE,   "MMMM d, y"),
        array(self::LOCALE_EN, \IntlDateFormatter::NONE,   \IntlDateFormatter::SHORT,  "h:mm a"),
        array(self::LOCALE_EN, \IntlDateFormatter::NONE,   \IntlDateFormatter::MEDIUM, "h:mm:ss a"),
        array(self::LOCALE_RU, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT,  "dd.MM.yyyy H:mm"),
        array(self::LOCALE_RU, \IntlDateFormatter::LONG,   \IntlDateFormatter::MEDIUM, "d MMMM y 'г.' H:mm:ss"),
        array(self::LOCALE_RU, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE,   "dd.MM.yyyy"),
        array(self::LOCALE_RU, \IntlDateFormatter::LONG,   \IntlDateFormatter::NONE,   "d MMMM y 'г.'"),
        array(self::LOCALE_RU, \IntlDateFormatter::NONE,   \IntlDateFormatter::SHORT,  "H:mm"),
        array(self::LOCALE_RU, \IntlDateFormatter::NONE,   \IntlDateFormatter::MEDIUM, "H:mm:ss"),
    );

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->setMethods(array('getLocale', 'getDatePattern'))
            ->getMock();
        $this->localeSettings->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue(self::LOCALE_EN));
        $localeSettings = $this->localeSettings;
        $localeSettings::staticExpects($this->any())
            ->method('getDatePattern')
            ->will($this->returnValueMap($this->localFormatMap));

        $this->converter = $this->createFormatConverter();
    }

    protected function tearDown()
    {
        unset($this->localeSettings);
        unset($this->converter);
    }

    /**
     * @return DateTimeFormatConverterInterface
     */
    abstract protected function createFormatConverter();

    /**
     * @param string $expected
     * @param string|null $locale
     * @param int|null $dateFormat
     * @dataProvider getDateFormatDataProvider
     */
    public function testGetDateFormat($expected, $locale = null, $dateFormat = null)
    {
        $this->assertEquals($expected, $this->converter->getDateFormat($locale, $dateFormat));
    }

    /**
     * @return array
     */
    abstract public function getDateFormatDataProvider();

    /**
     * @param string $expected
     * @param string|null $locale
     * @param int|null $timeFormat
     * @dataProvider getTimeFormatDataProvider
     */
    public function testGetTimeFormat($expected, $locale = null, $timeFormat = null)
    {
        $this->assertEquals($expected, $this->converter->getTimeFormat($locale, $timeFormat));
    }

    /**
     * @return array
     */
    abstract public function getTimeFormatDataProvider();

    /**
     * @param string $expected
     * @param string|null $locale
     * @param int|null $dateFormat
     * @param int|null $timeFormat
     * @dataProvider getDateTimeFormatDataProvider
     */
    public function testGetDateTimeFormat($expected, $locale = null, $dateFormat = null, $timeFormat = null)
    {
        $this->assertEquals($expected, $this->converter->getDateTimeFormat($locale, $dateFormat, $timeFormat));
    }

    /**
     * @return array
     */
    abstract public function getDateTimeFormatDataProvider();
}
