<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;

class NumberFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var NumberFormatter
     */
    protected $formatter;

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formatter = new NumberFormatter($this->localeSettings);
    }

    /**
     * @dataProvider formatDataProvider
     */
    public function testFormat($expected, $value, $style, $attributes, $textAttributes, $locale, $settingsLocale = null)
    {
        if ($settingsLocale) {
            $this->localeSettings->expects($this->once())->method('getLocale')
                ->will($this->returnValue($settingsLocale));
        }
        $this->assertEquals(
            $expected,
            $this->formatter->format($value, $style, $attributes, $textAttributes, $locale)
        );
    }

    public function formatDataProvider()
    {
        return array(
            array(
                'expected' => '1,234.568',
                'value' => 1234.56789,
                'style' => \NumberFormatter::DECIMAL,
                'attributes' => array(),
                'textAttributes' => array(),
                'locale' => 'en_US'
            ),
            array(
                'expected' => '1,234.57',
                'value' => 1234.56789,
                'style' => \NumberFormatter::DECIMAL,
                'attributes' => array(
                    'fraction_digits' => 2
                ),
                'textAttributes' => array(),
                'locale' => null,
                'settingsLocale' => 'en_US'
            ),
            array(
                'expected' => 'MINUS 10,0000.123',
                'value' => -100000.123,
                'style' => \NumberFormatter::DECIMAL,
                'attributes' => array(
                    \NumberFormatter::GROUPING_SIZE => 4,
                ),
                'textAttributes' => array(
                    \NumberFormatter::NEGATIVE_PREFIX => 'MINUS ',
                ),
                'locale' => 'en_US'
            ),
        );
    }

    public function testFormatWithoutLocale()
    {
        $settingsLocale = 'fr_FR';
        $this->localeSettings->expects($this->once())->method('getLocale')->will($this->returnValue($settingsLocale));
        $this->assertEquals(
            '123 456,4',
            $this->formatter->format(123456.4, \NumberFormatter::DECIMAL)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage NumberFormatter has no attribute 'UNKNOWN_ATTRIBUTE'
     */
    public function testFormatFails()
    {
        $this->formatter->format('123', \NumberFormatter::DECIMAL, array('unknown_attribute' => 1), array(), 'en_US');
    }

    /**
     * @dataProvider formatDecimalDataProvider
     */
    public function testFormatDecimal($expected, $value, $attributes, $textAttributes, $locale)
    {
        $this->assertEquals(
            $expected,
            $this->formatter->formatDecimal($value, $attributes, $textAttributes, $locale)
        );
    }

    public function formatDecimalDataProvider()
    {
        return array(
            array(
                'expected' => '1,234.568',
                'value' => 1234.56789,
                'attributes' => array(),
                'textAttributes' => array(),
                'locale' => 'en_US'
            ),
            array(
                'expected' => '+12,345.6789000000',
                'value' => 12345.6789,
                'attributes' => array(
                    'fraction_digits' => 10
                ),
                'textAttributes' => array(
                    'positive_prefix' => '+',
                ),
                'locale' => 'en_US'
            ),
        );
    }

    /**
     * @dataProvider formatCurrencyDataProvider
     */
    public function testFormatCurrency($expected, $value, $currency, $attributes, $textAttributes, $locale)
    {
        $this->assertEquals(
            $expected,
            $this->formatter->formatCurrency($value, $currency, $attributes, $textAttributes, $locale)
        );
    }

    public function formatCurrencyDataProvider()
    {
        return array(
            array(
                'expected' => '$1,234.57',
                'value' => 1234.56789,
                'currency' => 'USD',
                'attributes' => array(),
                'textAttributes' => array(),
                'locale' => 'en_US'
            ),
            array(
                'expected' => 'RUB1,234.57',
                'value' => 1234.56789,
                'currency' => 'RUB',
                'attributes' => array(),
                'textAttributes' => array(),
                'locale' => 'en_US'
            ),
        );
    }

    /**
     * @dataProvider formatPercentDataProvider
     */
    public function testFormatPercent($expected, $value, $attributes, $textAttributes, $locale)
    {
        $this->assertEquals(
            $expected,
            $this->formatter->formatPercent($value, $attributes, $textAttributes, $locale)
        );
    }

    public function formatPercentDataProvider()
    {
        return array(
            array(
                'expected' => '123,457%',
                'value' => 1234.56789,
                'attributes' => array(),
                'textAttributes' => array(),
                'locale' => 'en_US'
            ),
        );
    }
}
