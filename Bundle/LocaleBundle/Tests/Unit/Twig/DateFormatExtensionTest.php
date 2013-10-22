<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Model;

use Oro\Bundle\LocaleBundle\Twig\DateFormatExtension;

class DateFormatExtensionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TYPE = 'test_format_type';
    const TEST_FORMAT = 'MMM, d y t';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterRegistry;

    /**
     * @var DateFormatExtension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $expectedFunctions = array(
        'oro_dateformat' => 'getDateFormat',
        'oro_timeformat' => 'getTimeFormat',
        'oro_datetimeformat' => 'getDateTimeFormat',
    );

    protected function setUp()
    {
        $this->converterRegistry =
            $this->getMockBuilder('Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterRegistry')
                ->disableOriginalConstructor()
                ->setMethods(array('getFormatConverter'))
                ->getMock();

        $this->extension = new DateFormatExtension($this->converterRegistry);
    }

    protected function tearDown()
    {
        unset($this->converterRegistry);
        unset($this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_locale_dateformat', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $actualFunctions = $this->extension->getFunctions();
        $this->assertSameSize($this->expectedFunctions, $actualFunctions);

        /** @var $actualFunction \Twig_SimpleFunction */
        foreach ($actualFunctions as $actualFunction) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $actualFunction);
            $actualFunctionName = $actualFunction->getName();
            $this->assertArrayHasKey($actualFunctionName, $this->expectedFunctions);
            $expectedCallback = array($this->extension, $this->expectedFunctions[$actualFunctionName]);
            $this->assertEquals($expectedCallback, $actualFunction->getCallable());
        }
    }

    /**
     * @param int|null $expectedDateFormat
     * @param string|null $locale
     * @param string|null $dateFormat
     * @dataProvider getDateFormatDataProvider
     */
    public function testGetDateFormat($expectedDateFormat, $locale = null, $dateFormat = null)
    {
        $formatConverter = $this->createFormatConverter();
        $formatConverter->expects($this->once())
            ->method('getDateFormat')
            ->with($locale, $expectedDateFormat)
            ->will($this->returnValue(self::TEST_FORMAT));

        $this->converterRegistry->expects($this->once())
            ->method('getFormatConverter')
            ->with(self::TEST_TYPE)
            ->will($this->returnValue($formatConverter));

        $this->assertEquals(self::TEST_FORMAT, $this->extension->getDateFormat(self::TEST_TYPE, $locale, $dateFormat));
    }

    /**
     * @return array
     */
    public function getDateFormatDataProvider()
    {
        return array(
            'default data' => array(
                'expectedDateFormat' => null,
            ),
            'incorrect format' => array(
                'expectedDateFormat' => null,
                'locale' => 'en',
                'dateFormat' => 'someUnknownFormat',
            ),
            'none format' => array(
                'expectedDateFormat' => \IntlDateFormatter::NONE,
                'locale' => 'en',
                'dateFormat' => 'none',
            ),
            'short format' => array(
                'expectedDateFormat' => \IntlDateFormatter::SHORT,
                'locale' => 'en',
                'dateFormat' => 'short',
            ),
            'medium format' => array(
                'expectedDateFormat' => \IntlDateFormatter::MEDIUM,
                'locale' => 'en',
                'dateFormat' => 'medium',
            ),
            'long format' => array(
                'expectedDateFormat' => \IntlDateFormatter::LONG,
                'locale' => 'en',
                'dateFormat' => 'long',
            ),
            'full format' => array(
                'expectedDateFormat' => \IntlDateFormatter::FULL,
                'locale' => 'en',
                'dateFormat' => 'full',
            ),
        );
    }

    public function testGetTimeFormat()
    {
        $locale = 'en';
        $timeFormat = 'short';
        $expectedTimeFormat = \IntlDateFormatter::SHORT;

        $formatConverter = $this->createFormatConverter();
        $formatConverter->expects($this->once())
            ->method('getTimeFormat')
            ->with($locale, $expectedTimeFormat)
            ->will($this->returnValue(self::TEST_FORMAT));

        $this->converterRegistry->expects($this->once())
            ->method('getFormatConverter')
            ->with(self::TEST_TYPE)
            ->will($this->returnValue($formatConverter));

        $this->assertEquals(self::TEST_FORMAT, $this->extension->getTimeFormat(self::TEST_TYPE, $locale, $timeFormat));
    }

    public function testGetDateTimeFormat()
    {
        $locale = 'en';
        $dateFormat = 'medium';
        $timeFormat = 'short';
        $expectedDateFormat = \IntlDateFormatter::MEDIUM;
        $expectedTimeFormat = \IntlDateFormatter::SHORT;

        $formatConverter = $this->createFormatConverter();
        $formatConverter->expects($this->once())
            ->method('getDateTimeFormat')
            ->with($locale, $expectedDateFormat, $expectedTimeFormat)
            ->will($this->returnValue(self::TEST_FORMAT));

        $this->converterRegistry->expects($this->once())
            ->method('getFormatConverter')
            ->with(self::TEST_TYPE)
            ->will($this->returnValue($formatConverter));

        $this->assertEquals(
            self::TEST_FORMAT,
            $this->extension->getDateTimeFormat(self::TEST_TYPE, $locale, $dateFormat, $timeFormat)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFormatConverter()
    {
        return $this->getMockBuilder('Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }
}
