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
    public function testFormat(
        $expected,
        $value,
        $style,
        $attributes,
        $textAttributes,
        $symbols,
        $locale,
        $defaultLocale = null
    ) {
        if ($defaultLocale) {
            $this->localeSettings->expects($this->once())->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        }
        $this->assertEquals(
            $expected,
            $this->formatter->format($value, $style, $attributes, $textAttributes, $symbols, $locale)
        );
    }

    public function formatDataProvider()
    {
        return [
            [
                'expected'       => '1,234.568',
                'value'          => 1234.56789,
                'style'          => \NumberFormatter::DECIMAL,
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'en_US'
            ],
            [
                'expected'       => '1,234.568',
                'value'          => 1234.56789,
                'style'          => 'DECIMAL',
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'en_US'
            ],
            [
                'expected'   => '1,234.57',
                'value'      => 1234.56789,
                'style'      => \NumberFormatter::DECIMAL,
                'attributes' => [
                    'fraction_digits' => 2
                ],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => null,
                'settingsLocale' => 'en_US'
            ],
            [
                'expected'   => 'MINUS 10.0000,123',
                'value'      => -100000.123,
                'style'      => \NumberFormatter::DECIMAL,
                'attributes' => [
                    \NumberFormatter::GROUPING_SIZE => 4,
                ],
                'textAttributes' => [
                    \NumberFormatter::NEGATIVE_PREFIX => 'MINUS ',
                ],
                'symbols' => [
                    \NumberFormatter::DECIMAL_SEPARATOR_SYMBOL  => ',',
                    \NumberFormatter::GROUPING_SEPARATOR_SYMBOL => '.',
                ],
                'locale' => 'en_US'
            ],
        ];
    }

    public function testFormatWithoutLocale()
    {
        $locale = 'fr_FR';
        $this->localeSettings->expects($this->once())->method('getLocale')->will($this->returnValue($locale));
        $this->assertEquals(
            '123 456,4',
            $this->formatter->format(123456.4, \NumberFormatter::DECIMAL)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage NumberFormatter has no constant 'UNKNOWN_ATTRIBUTE'
     */
    public function testFormatFails()
    {
        $this->formatter->format(
            '123',
            \NumberFormatter::DECIMAL,
            ['unknown_attribute' => 1],
            [],
            [],
            'en_US'
        );
    }

    /**
     * @dataProvider formatDecimalDataProvider
     */
    public function testFormatDecimal($expected, $value, $attributes, $textAttributes, $symbols, $locale)
    {
        $this->assertEquals(
            $expected,
            $this->formatter->formatDecimal($value, $attributes, $textAttributes, $symbols, $locale)
        );
    }

    public function formatDecimalDataProvider()
    {
        return [
            [
                'expected'       => '1,234.568',
                'value'          => 1234.56789,
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'en_US'
            ],
            [
                'expected'   => '+12 345,6789000000',
                'value'      => 12345.6789,
                'attributes' => [
                    'fraction_digits' => 10
                ],
                'textAttributes' => [
                    'positive_prefix' => '+',
                ],
                'symbols' => [
                    \NumberFormatter::DECIMAL_SEPARATOR_SYMBOL  => ',',
                    \NumberFormatter::GROUPING_SEPARATOR_SYMBOL => ' ',
                ],
                'locale' => 'en_US'
            ],
        ];
    }

    public function testDefaultFormatCurrency()
    {
        $locale = 'en_GB';
        $currency = 'GBP';
        $currencySymbol = 'Pound';

        $this->localeSettings->expects($this->any())->method('getLocale')->will($this->returnValue($locale));
        $this->localeSettings->expects($this->any())->method('getCurrency')->will($this->returnValue($currency));
        $this->localeSettings->expects($this->any())
            ->method('getCurrencySymbolByCurrency')
            ->with($currency)
            ->will($this->returnValue($currencySymbol));

        $this->assertEquals('Pound1,234.57', $this->formatter->formatCurrency(1234.56789));
    }

    /**
     * @dataProvider formatCurrencyDataProvider
     */
    public function testFormatCurrency($expected, $value, $currency, $attributes, $textAttributes, $symbols, $locale)
    {
        $currencySymbolMap = [
            ['USD', '$'],
            ['RUB', 'руб.'],
        ];
        $this->localeSettings->expects($this->any())
            ->method('getCurrencySymbolByCurrency')
            ->will($this->returnValueMap($currencySymbolMap));

        $this->assertEquals(
            $expected,
            $this->formatter->formatCurrency($value, $currency, $attributes, $textAttributes, $symbols, $locale)
        );
    }

    public function formatCurrencyDataProvider()
    {
        return [
            [
                'expected'       => '$1,234.57',
                'value'          => 1234.56789,
                'currency'       => 'USD',
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'en_US'
            ],
            [
                'expected'       => 'руб.1,234.57',
                'value'          => 1234.56789,
                'currency'       => 'RUB',
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'en_US'
            ],
            [
                'expected'       => '1 234,57 €',
                'value'          => 1234.56789,
                'currency'       => 'EUR',
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'ru_RU'
            ],
        ];
    }

    /**
     * @dataProvider formatPercentDataProvider
     */
    public function testFormatPercent($expected, $value, $attributes, $textAttributes, $symbols, $locale)
    {
        $this->assertEquals(
            $expected,
            $this->formatter->formatPercent($value, $attributes, $textAttributes, $symbols, $locale)
        );
    }

    public function formatPercentDataProvider()
    {
        return [
            [
                'expected'       => '123,457%',
                'value'          => 1234.56789,
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'en_US'
            ],
        ];
    }

    /**
     * @dataProvider formatSpelloutDataProvider
     */
    public function testFormatSpellout($expected, $value, $attributes, $textAttributes, $symbols, $locale)
    {
        $this->assertEquals(
            $expected,
            $this->formatter->formatSpellout($value, $attributes, $textAttributes, $symbols, $locale)
        );
    }

    public function formatSpelloutDataProvider()
    {
        return [
            [
                'expected'       => 'twenty-one',
                'value'          => 21,
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'en_US'
            ],
        ];
    }

    /**
     * @dataProvider formatDurationDataProvider
     */
    public function testFormatDuration($expected, $value, $attributes, $textAttributes, $symbols, $locale)
    {
        $this->assertEquals(
            $expected,
            $this->formatter->formatDuration($value, $attributes, $textAttributes, $symbols, $locale)
        );
    }

    public function formatDurationDataProvider()
    {
        return [
            [
                'expected'       => '1:01:01',
                'value'          => 3661,
                'attributes'     => [],
                'textAttributes' => [],
                'symbols'        => [],
                'locale'         => 'en_US'
            ],
            [
                'expected'       => '1 hour, 1 minute, 1 second',
                'value'          => 3661,
                'attributes'     => [],
                'textAttributes' => [
                    \NumberFormatter::DEFAULT_RULESET => "%with-words"
                ],
                'symbols' => [],
                'locale'  => 'en_US'
            ],
        ];
    }

    public function testFormatOrdinal()
    {
        $result = $this->formatter->formatOrdinal(1, [], [], [], 'en_US');

        // expected result is: 1st but in som versions of ICU 1ˢᵗ is also possible
        $this->assertStringStartsWith('1', $result);
        $this->assertNotEquals('1', $result);
    }

    /**
     * @dataProvider getAttributeDataProvider
     */
    public function testGetAttribute($attribute, $style, $locale, $expected)
    {
        $this->assertSame(
            $expected,
            $this->formatter->getAttribute(
                $attribute,
                $style,
                $locale
            )
        );
    }

    public function getAttributeDataProvider()
    {
        return [
            ['parse_int_only', 'DECIMAL', 'en_US', 0],
            ['parse_int_only', null, 'en_US', 0],
            ['GROUPING_USED', 'decimal', 'en_US', 1],
            [\NumberFormatter::DECIMAL_ALWAYS_SHOWN, \NumberFormatter::DECIMAL, 'en_US', 0],
            [\NumberFormatter::MAX_INTEGER_DIGITS, \NumberFormatter::DECIMAL, 'en_US', 309],
            [\NumberFormatter::MIN_INTEGER_DIGITS, \NumberFormatter::DECIMAL, 'en_US', 1],
            [\NumberFormatter::INTEGER_DIGITS,\NumberFormatter::DECIMAL, 'en_US', 1],
            [\NumberFormatter::MAX_FRACTION_DIGITS, \NumberFormatter::DECIMAL, 'en_US', 3],
            [\NumberFormatter::MIN_FRACTION_DIGITS, \NumberFormatter::DECIMAL, 'en_US', 0],
            [\NumberFormatter::MAX_FRACTION_DIGITS, \NumberFormatter::CURRENCY, 'en_US', 2],
            [\NumberFormatter::MIN_FRACTION_DIGITS, \NumberFormatter::CURRENCY, 'en_US', 2],
            [\NumberFormatter::FRACTION_DIGITS, \NumberFormatter::DECIMAL, 'en_US', 0],
            [\NumberFormatter::MULTIPLIER, \NumberFormatter::DECIMAL, 'en_US', 1],
            [\NumberFormatter::GROUPING_SIZE, \NumberFormatter::DECIMAL, 'en_US', 3],
            [\NumberFormatter::ROUNDING_MODE, \NumberFormatter::DECIMAL, 'en_US', 4],
            [\NumberFormatter::ROUNDING_INCREMENT, \NumberFormatter::DECIMAL, 'en_US', 0.0],
            [\NumberFormatter::FORMAT_WIDTH, \NumberFormatter::DECIMAL, 'en_US', 0],
            [\NumberFormatter::PADDING_POSITION, \NumberFormatter::DECIMAL, 'en_US', 0],
            [\NumberFormatter::SECONDARY_GROUPING_SIZE, \NumberFormatter::DECIMAL, 'en_US', 0],
            [\NumberFormatter::SIGNIFICANT_DIGITS_USED, \NumberFormatter::DECIMAL, 'en_US', 0],
            [\NumberFormatter::MIN_SIGNIFICANT_DIGITS, \NumberFormatter::DECIMAL, 'en_US', 1],
            [\NumberFormatter::MAX_SIGNIFICANT_DIGITS, \NumberFormatter::DECIMAL, 'en_US', 6],
        ];
    }

    /**
     * @dataProvider getTextAttributeDataProvider
     */
    public function testTextAttribute($attribute, $locale, $style, $expected)
    {
        $this->assertSame(
            $expected,
            $this->formatter->getTextAttribute(
                $attribute,
                $locale,
                $style
            )
        );
    }

    public function getTextAttributeDataProvider()
    {
        return [
            ['POSITIVE_PREFIX', 'DECIMAL', 'en_US', ''],
            ['negative_prefix', 'decimal', 'en_US', '-'],
            [\NumberFormatter::NEGATIVE_SUFFIX, \NumberFormatter::DECIMAL, 'en_US', ''],
            [\NumberFormatter::PADDING_CHARACTER, \NumberFormatter::DECIMAL, 'en_US', '*'],
            [\NumberFormatter::CURRENCY_CODE, \NumberFormatter::CURRENCY, 'en_US', 'USD'],
            [\NumberFormatter::DEFAULT_RULESET, \NumberFormatter::DECIMAL, 'en_US', false],
            [\NumberFormatter::PUBLIC_RULESETS, \NumberFormatter::DECIMAL, 'en_US', false]
        ];
    }

    /**
     * @dataProvider getSymbolDataProvider
     */
    public function testGetNumberFormatterSymbol($symbol, $locale, $style, $expected)
    {
        $this->assertSame(
            $expected,
            $this->formatter->getSymbol(
                $symbol,
                $locale,
                $style
            )
        );
    }

    public function getSymbolDataProvider()
    {
        return [
            ['DECIMAL_SEPARATOR_SYMBOL', 'DECIMAL', 'en_US', '.'],
            [\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', ','],
            ['pattern_separator_symbol', 'decimal', 'en_US', ';'],
            [\NumberFormatter::PERCENT_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '%'],
            [\NumberFormatter::ZERO_DIGIT_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '0'],
            [\NumberFormatter::DIGIT_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '#'],
            [\NumberFormatter::MINUS_SIGN_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '-'],
            [\NumberFormatter::PLUS_SIGN_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '+'],
            [\NumberFormatter::CURRENCY_SYMBOL, \NumberFormatter::CURRENCY, 'en_US', '$'],
            [\NumberFormatter::INTL_CURRENCY_SYMBOL, \NumberFormatter::CURRENCY, 'en_US', 'USD'],
            [\NumberFormatter::MONETARY_SEPARATOR_SYMBOL, \NumberFormatter::CURRENCY, 'en_US', '.'],
            [\NumberFormatter::EXPONENTIAL_SYMBOL, \NumberFormatter::SCIENTIFIC, 'en_US', 'E'],
            [\NumberFormatter::PERMILL_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '‰'],
            [\NumberFormatter::PAD_ESCAPE_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '*'],
            [\NumberFormatter::INFINITY_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '∞'],
            [\NumberFormatter::NAN_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', 'NaN'],
            [\NumberFormatter::SIGNIFICANT_DIGIT_SYMBOL, \NumberFormatter::DECIMAL, 'en_US', '@'],
            [\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, \NumberFormatter::CURRENCY, 'en_US', ','],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage NumberFormatter style '19' is invalid
     */
    public function testFormatWithInvalidStyle()
    {
        $this->formatter->format(123, \NumberFormatter::LENIENT_PARSE);
    }

    /**
     * @param bool $expected
     * @param string $currency
     * @param string|null $locale
     * @param string|null $defaultLocale
     * @dataProvider isCurrencySymbolPrependDataProvider
     */
    public function testIsCurrencySymbolPrepend($expected, $currency, $locale, $defaultLocale = null)
    {
        if ($defaultLocale) {
            $this->localeSettings->expects($this->once())
                ->method('getLocale')
                ->will($this->returnValue($defaultLocale));
        } else {
            $this->localeSettings->expects($this->never())
                ->method('getLocale');
        }

        $this->assertEquals($expected, $this->formatter->isCurrencySymbolPrepend($currency, $locale));
    }

    /**
     * @return array
     */
    public function isCurrencySymbolPrependDataProvider()
    {
        return [
            'default locale' => [
                'expected'      => true,
                'currency'      => 'USD',
                'locale'        => null,
                'defaultLocale' => 'en',
            ],
            'custom locale' => [
                'expected' => false,
                'currency' => 'RUR',
                'locale'   => 'ru',
            ],
        ];
    }
}
