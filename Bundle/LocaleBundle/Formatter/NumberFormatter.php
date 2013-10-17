<?php

namespace Oro\Bundle\LocaleBundle\Formatter;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use NumberFormatter as IntlNumberFormatter;

class NumberFormatter
{
    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * Format number
     *
     * @param int|float $value
     * @param int $style Constant of \NumberFormatter (DECIMAL, CURRENCY, PERCENT, etc)
     * @param array $attributes Set of attributes of \NumberFormatter
     * @param array $textAttributes Set of text attributes of \NumberFormatter
     * @param string|null $locale Locale of formatting
     * @return string
     */
    public function format($value, $style, array $attributes = array(), array $textAttributes = array(), $locale = null)
    {
        return $this->getFormatter($locale, $style, $attributes, $textAttributes)->format($value);
    }

    /**
     * Format currency
     *
     * @param float $value
     * @param string $currency Currency code
     * @param array $attributes Set of attributes of \NumberFormatter
     * @param array $textAttributes Set of text attributes of \NumberFormatter
     * @param string|null $locale Locale of formatting
     * @return string
     */
    public function formatCurrency(
        $value,
        $currency,
        array $attributes = array(),
        array $textAttributes = array(),
        $locale = null
    ) {
        return $this->getFormatter($locale, \NumberFormatter::CURRENCY, $attributes, $textAttributes)
            ->formatCurrency($value, $currency);
    }

    /**
     * Format decimal
     *
     * @param float $value
     * @param array $attributes Set of attributes of \NumberFormatter
     * @param array $textAttributes Set of text attributes of \NumberFormatter
     * @param string|null $locale Locale of formatting
     * @return string
     */
    public function formatDecimal($value, array $attributes = array(), array $textAttributes = array(), $locale = null)
    {
        return $this->getFormatter($locale, \NumberFormatter::DECIMAL, $attributes, $textAttributes)->format($value);
    }

    /**
     * Format percent
     *
     * @param float $value
     * @param array $attributes Set of attributes of \NumberFormatter
     * @param array $textAttributes Set of text attributes of \NumberFormatter
     * @param string|null $locale Locale of formatting
     * @return string
     */
    public function formatPercent($value, array $attributes = array(), array $textAttributes = array(), $locale = null)
    {
        return $this->getFormatter($locale, \NumberFormatter::PERCENT, $attributes, $textAttributes)->format($value);
    }

    /**
     * Creates instance of NumberFormatter class of intl extension
     *
     * @param string $locale
     * @param int $style
     * @param array $attributes
     * @param array $textAttributes
     * @return IntlNumberFormatter
     * @throws \InvalidArgumentException
     */
    public function getFormatter($locale, $style, array $attributes = array(), array $textAttributes = array())
    {
        if (!$locale) {
            $locale = $this->localeSettings->getLocale();
        }

        $formatter = new IntlNumberFormatter($locale, $style);

        foreach ($this->parseAttributes($attributes) as $attribute => $value) {
            $formatter->setAttribute($attribute, $value);
        }

        foreach ($this->parseAttributes($textAttributes) as $attribute => $value) {
            $formatter->setTextAttribute($attribute, $value);
        }

        return $formatter;
    }

    /**
     * Converts keys of attributes array to values of NumberFormatter constants
     *
     * @param array $attributes
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function parseAttributes(array $attributes)
    {
        $result = array();
        foreach ($attributes as $attributeName => $value) {
            if (is_int($attributeName)) {
                $attribute = $attributeName;
            } else {
                $attributeName = strtoupper($attributeName);
                $constantName = 'NumberFormatter::' . $attributeName;
                if (!defined($constantName)) {
                    throw new \InvalidArgumentException("NumberFormatter has no attribute '$attributeName'");
                }
                $attribute = constant($constantName);
            }
            $result[$attribute] = $value;
        }
        return $result;
    }
}
