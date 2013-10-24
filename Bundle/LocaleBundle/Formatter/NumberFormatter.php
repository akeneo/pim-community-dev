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
                $this->parseConstantValue($style),
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
     * Gets value of numeric attribute of \NumberFormatter
     *
     * Supported numeric attribute constants of \NumberFormatter are:
     *  PARSE_INT_ONLY
     *  GROUPING_USED
     *  DECIMAL_ALWAYS_SHOWN
     *  MAX_INTEGER_DIGITS
     *  MIN_INTEGER_DIGITS
     *  INTEGER_DIGITS
     *  MAX_FRACTION_DIGITS
     *  MIN_FRACTION_DIGITS
     *  FRACTION_DIGITS
     *  MULTIPLIER
     *  GROUPING_SIZE
     *  ROUNDING_MODE
     *  ROUNDING_INCREMENT
     *  FORMAT_WIDTH
     *  PADDING_POSITION
     *  SECONDARY_GROUPING_SIZE
     *  SIGNIFICANT_DIGITS_USED
     *  MIN_SIGNIFICANT_DIGITS
     *  MAX_SIGNIFICANT_DIGITS
     *  LENIENT_PARSE
     *
     * @param int|string $attribute Numeric attribute constant of \NumberFormatter or it's string name
     * @param int|string $style Constant of \NumberFormatter (DECIMAL, CURRENCY, PERCENT, etc) or string name
     * @param string|null $locale
     * @return bool|int
     */
    public function getAttribute($attribute, $style = null, $locale = null)
    {
        return $this->getFormatter(
            $locale,
            $this->parseStyle($style)
        )->getAttribute($this->parseConstantValue($attribute));
    }

    /**
     * Gets value of text attribute of \NumberFormatter
     *
     * Supported text attribute constants of \NumberFormatter are:
     *  POSITIVE_PREFIX
     *  POSITIVE_SUFFIX
     *  NEGATIVE_PREFIX
     *  NEGATIVE_SUFFIX
     *  PADDING_CHARACTER
     *  CURRENCY_CODE
     *  DEFAULT_RULESET
     *  PUBLIC_RULESETS
     *
     * @param int|string $attribute Text attribute constant of \NumberFormatter or it's string name
     * @param int|string $style Constant of \NumberFormatter (DECIMAL, CURRENCY, PERCENT, etc) or string name
     * @param string|null $locale
     * @return bool|int
     */
    public function getTextAttribute($attribute, $style, $locale = null)
    {
        return $this->getFormatter(
            $locale,
            $this->parseStyle($style)
        )->getTextAttribute($this->parseConstantValue($attribute));
    }

    /**
     * Gets value of symbol associated with \NumberFormatter
     *
     * Supported symbol constants of \NumberFormatter are:
     *  DECIMAL_SEPARATOR_SYMBOL
     *  GROUPING_SEPARATOR_SYMBOL
     *  PATTERN_SEPARATOR_SYMBOL
     *  PERCENT_SYMBOL
     *  ZERO_DIGIT_SYMBOL
     *  DIGIT_SYMBOL
     *  MINUS_SIGN_SYMBOL
     *  PLUS_SIGN_SYMBOL
     *  CURRENCY_SYMBOL
     *  INTL_CURRENCY_SYMBOL
     *  MONETARY_SEPARATOR_SYMBOL
     *  EXPONENTIAL_SYMBOL
     *  PERMILL_SYMBOL
     *  PAD_ESCAPE_SYMBOL
     *  INFINITY_SYMBOL
     *  NAN_SYMBOL
     *  SIGNIFICANT_DIGIT_SYMBOL
     *  MONETARY_GROUPING_SEPARATOR_SYMBOL
     *
     *
     * @param int|string $symbol Format symbol constant of \NumberFormatter or it's string name
     * @param int|string $style Constant of \NumberFormatter (DECIMAL, CURRENCY, PERCENT, etc) or string name
     * @param string|null $locale
     * @return bool|int
     */
    public function getSymbol($symbol, $style, $locale = null)
    {
        return $this->getFormatter(
            $locale,
            $this->parseStyle($style)
        )->getSymbol($this->parseConstantValue($symbol));
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
    protected function getFormatter($locale, $style, array $attributes = array(), array $textAttributes = array())
    {
        $formatter = new IntlNumberFormatter(
            $locale ? : $this->localeSettings->getLocale(),
            $this->parseStyle($style)
        );

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
            $result[$this->parseConstantValue($attribute)] = $value;
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
    protected function parseConstantValue($attribute)
    {
        if (is_int($attribute)) {
            return $attribute;
        } else {
            $attributeName = strtoupper($attribute);
            $constantName = 'NumberFormatter::' . $attributeName;
            if (!defined($constantName)) {
                throw new \InvalidArgumentException("NumberFormatter has no constant '$attributeName'");
            }
            return constant($constantName);
        }
    }

    /**
     * Pass style of NumberFormatter
     *
     * @param int|string|null $style
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function parseStyle($style)
    {
        $originalValue = $style;
        if (null === $style) {
            $style = \NumberFormatter::DEFAULT_STYLE;
        }
        $style = $this->parseConstantValue($style);

        $styleConstants = array(
            \NumberFormatter::PATTERN_DECIMAL,
            \NumberFormatter::DECIMAL,
            \NumberFormatter::CURRENCY,
            \NumberFormatter::PERCENT,
            \NumberFormatter::SCIENTIFIC,
            \NumberFormatter::SPELLOUT,
            \NumberFormatter::ORDINAL,
            \NumberFormatter::DURATION,
            \NumberFormatter::PATTERN_RULEBASED,
            \NumberFormatter::IGNORE,
            \NumberFormatter::DEFAULT_STYLE,
        );

        if (!in_array($style, $styleConstants)) {
            throw new \InvalidArgumentException("NumberFormatter style '$originalValue' is invalid");
        }

        return $style;
    }
}
