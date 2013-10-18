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

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * Format number
     *
     * @param int|float $value
     * @param string|int $style Constant of \NumberFormatter (DECIMAL, CURRENCY, PERCENT, etc) or string name
     * @param array $attributes Set of attributes of \NumberFormatter
     * @param array $textAttributes Set of text attributes of \NumberFormatter
     * @param string|null $locale Locale of formatting
     * @return string
     */
    public function format($value, $style, array $attributes = array(), array $textAttributes = array(), $locale = null)
    {
        return
            $this->getFormatter(
                $locale,
                $this->parseAttribute($style),
                $attributes,
                $textAttributes
            )->format($value);
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
        $currency = null,
        array $attributes = array(),
        array $textAttributes = array(),
        $locale = null
    ) {
        if (!$currency) {
            $currency = $this->localeSettings->getCurrency();
        }
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
        return $this->format($value, \NumberFormatter::DECIMAL, $attributes, $textAttributes, $locale);
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
        return $this->format($value, \NumberFormatter::PERCENT, $attributes, $textAttributes, $locale);
    }

    /**
     * Format spellout
     *
     * @param float $value
     * @param array $attributes Set of attributes of \NumberFormatter
     * @param array $textAttributes Set of text attributes of \NumberFormatter
     * @param string|null $locale Locale of formatting
     * @return string
     */
    public function formatSpellout($value, array $attributes = array(), array $textAttributes = array(), $locale = null)
    {
        return $this->format($value, \NumberFormatter::SPELLOUT, $attributes, $textAttributes, $locale);
    }

    /**
     * Format duration
     *
     * @param float $value
     * @param array $attributes Set of attributes of \NumberFormatter
     * @param array $textAttributes Set of text attributes of \NumberFormatter
     * @param string|null $locale Locale of formatting
     * @return string
     */
    public function formatDuration($value, array $attributes = array(), array $textAttributes = array(), $locale = null)
    {
        return $this->format($value, \NumberFormatter::DURATION, $attributes, $textAttributes, $locale);
    }

    /**
     * Format ordinal
     *
     * @param float $value
     * @param array $attributes Set of attributes of \NumberFormatter
     * @param array $textAttributes Set of text attributes of \NumberFormatter
     * @param string|null $locale Locale of formatting
     * @return string
     */
    public function formatOrdinal($value, array $attributes = array(), array $textAttributes = array(), $locale = null)
    {
        return $this->format($value, \NumberFormatter::ORDINAL, $attributes, $textAttributes, $locale);
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
        foreach ($attributes as $attribute => $value) {
            $result[$this->parseAttribute($attribute)] = $value;
        }
        return $result;
    }

    /**
     * Pass value of NumberFormatter constant or it's string name and get value
     *
     * @param int|string $attribute
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function parseAttribute($attribute)
    {
        if (is_int($attribute)) {
            return $attribute;
        } else {
            $attributeName = strtoupper($attribute);
            $constantName = 'NumberFormatter::' . $attributeName;
            if (!defined($constantName)) {
                throw new \InvalidArgumentException("NumberFormatter has no attribute '$attributeName'");
            }
            return constant($constantName);
        }
    }
}
