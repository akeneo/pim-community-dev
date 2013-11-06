Number Formatting
=================

Table of Contents
-----------------
 - [PHP Number Formatter](#php-number-formatter)
   - [Methods and examples of usage](#methods-and-examples-of-usage)
     - [format](#format)
     - [formatCurrency](#formatCurrency)
     - [formatDecimal](#formatDecimal)
     - [formatPercent](#formatPercent)
     - [formatSpellout](#formatSpellout)
     - [formatOrdinal](#formatOrdinal)
     - [getAttribute](#getAttribute)
     - [getTextAttribute](#getTextAttribute)
     - [getSymbol](#getSymbol)
  - [Twig](#twig)
  - [JS](#js)

PHP Number Formatter
====================

**Class:** Oro\Bundle\LocaleBundle\Formatter\NumberFormatter

**Service id:** oro_locale.formatter.number

Formats different styles of numbers in localized format. Proxies intl extension class [NumberFormatter](http://www.php.net/manual/en/class.numberformatter.php).
Method of this class can receive values of original intl NumberFormatter constants. These constants can be divided
by next logical groups:

**Format style constants**
```
\NumberFormatter::PATTERN_DECIMAL
\NumberFormatter::DECIMAL
\NumberFormatter::CURRENCY
\NumberFormatter::PERCENT
\NumberFormatter::SCIENTIFIC
\NumberFormatter::SPELLOUT
\NumberFormatter::ORDINAL
\NumberFormatter::DURATION
\NumberFormatter::PATTERN_RULEBASED
\NumberFormatter::IGNORE
\NumberFormatter::DEFAULT_STYLE
```

**Numeric attribute constants**
```
\NumberFormatter::PARSE_INT_ONLY
\NumberFormatter::GROUPING_USED
\NumberFormatter::DECIMAL_ALWAYS_SHOWN
\NumberFormatter::MAX_INTEGER_DIGITS
\NumberFormatter::MIN_INTEGER_DIGITS
\NumberFormatter::INTEGER_DIGITS
\NumberFormatter::MAX_FRACTION_DIGITS
\NumberFormatter::MIN_FRACTION_DIGITS
\NumberFormatter::FRACTION_DIGITS
\NumberFormatter::MULTIPLIER
\NumberFormatter::GROUPING_SIZE
\NumberFormatter::ROUNDING_MODE
\NumberFormatter::ROUNDING_INCREMENT
\NumberFormatter::FORMAT_WIDTH
\NumberFormatter::PADDING_POSITION
\NumberFormatter::SECONDARY_GROUPING_SIZE
\NumberFormatter::SIGNIFICANT_DIGITS_USED
\NumberFormatter::MIN_SIGNIFICANT_DIGITS
\NumberFormatter::MAX_SIGNIFICANT_DIGITS
\NumberFormatter::LENIENT_PARSE
```

**Text attribute constants**
```
\NumberFormatter::POSITIVE_PREFIX
\NumberFormatter::POSITIVE_SUFFIX
\NumberFormatter::NEGATIVE_PREFIX
\NumberFormatter::NEGATIVE_SUFFIX
\NumberFormatter::PADDING_CHARACTER
\NumberFormatter::CURRENCY_CODE
\NumberFormatter::DEFAULT_RULESET
\NumberFormatter::PUBLIC_RULESETS
```

**Format symbol constants**
```
\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL
\NumberFormatter::GROUPING_SEPARATOR_SYMBOL
\NumberFormatter::PATTERN_SEPARATOR_SYMBOL
\NumberFormatter::PERCENT_SYMBOL
\NumberFormatter::ZERO_DIGIT_SYMBOL
\NumberFormatter::DIGIT_SYMBOL
\NumberFormatter::MINUS_SIGN_SYMBOL
\NumberFormatter::PLUS_SIGN_SYMBOL
\NumberFormatter::CURRENCY_SYMBOL
\NumberFormatter::INTL_CURRENCY_SYMBOL
\NumberFormatter::MONETARY_SEPARATOR_SYMBOL
\NumberFormatter::EXPONENTIAL_SYMBOL
\NumberFormatter::PERMILL_SYMBOL
\NumberFormatter::PAD_ESCAPE_SYMBOL
\NumberFormatter::INFINITY_SYMBOL
\NumberFormatter::NAN_SYMBOL
\NumberFormatter::SIGNIFICANT_DIGIT_SYMBOL
\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL
```

Each constant can be passed to appropriate method of Oro\Bundle\LocaleBundle\Formatter\NumberFormatter as a string name,
for example, case insensitive: "DECIMAL_SEPARATOR_SYMBOL", "currency_code".

Methods and examples of usage
-----------------------------

### format

string *public* *format*(mixed *value*, string|int *style*[, array *attributes*[, array *textAttributes*[, array *symbols*[, string *locale*]]]])

This method can be used to format any style of number that are passed directly as a second argument.
List of custom attributes, text attributes, symbols and locale can be passed as well.

```
// Simple usage default locale and related number format will be used
echo $numberFormatter->format(1234.56789, \NumberFormatter::DECIMAL);
// outputs: "1,234.568" if default locale is en_US

// Use custom attributes and custom locale
echo $numberFormatter->format(
    -100000.123,
    \NumberFormatter::DECIMAL,
    'attributes' => array(\NumberFormatter::GROUPING_SIZE => 4),
    'textAttributes' => array(\NumberFormatter::NEGATIVE_PREFIX => 'MINUS '),
    'symbols' => array(
        \NumberFormatter::DECIMAL_SEPARATOR_SYMBOL => ',',
        \NumberFormatter::GROUPING_SEPARATOR_SYMBOL => '.',
    ),
);
// outputs: "MINUS 10.0000,123"

```

### formatCurrency

string *public* *formatCurrency*(mixed *value*, string *currency*[, array *attributes*[, array *textAttributes*[, array *symbols*[, string *locale*]]]])

Formats currency number. Currency code should be specified, otherwise default currency will be used.

```
// Using default locale and currency
echo $numberFormatter->formatCurrency(1234.56789);
// outputs: "$1,234.57" if default locale is en_US and currency is 'USD'

// Specify custom currency and locale
echo $numberFormatter->formatCurrency(1234.56789, 'EUR', array(), array(), array(), 'ru_RU');
// outputs: "1 234,57 €"
```

### formatDecimal

string *public* *formatDecimal*(mixed *value*[, array *attributes*[, array *textAttributes*[, array *symbols*[, string *locale*]]]])

Formats decimal number.

```
// Using default locale and format
echo $numberFormatter->formatDecimal(1234.56789);
// outputs: "1,234.568" if default locale is en_US and currency is 'USD'

// Specify custom locale and attributes
echo $numberFormatter->formatDecimal(
    1234.56789,
    'attributes' => array('fraction_digits' => 10),
    'textAttributes' => array('positive_prefix' => '+',),
    'symbols' => array(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL => ',', \NumberFormatter::GROUPING_SEPARATOR_SYMBOL => ' '),
    'en_US'
);
// outputs: "+12 345,6789000000"
```

### formatPercent

string *public* *formatPercent*(mixed *value*[, array *attributes*[, array *textAttributes*[, array *symbols*[, string *locale*]]]])

Formats percent number.

```
echo $numberFormatter->formatDecimal(1);
// outputs: "100%"

echo $numberFormatter->formatDecimal(.567, array(), array(), array(), 'en_US');
// outputs: "56,7%"
```

### formatSpellout

string *public* *formatSpellout*(mixed *value*[, array *attributes*[, array *textAttributes*[, array *symbols*[, string *locale*]]]])

Formats spellout number. If locale is not specified default one will be used.

```
echo $numberFormatter->formatSpellout(1);
// outputs: "one"

echo $numberFormatter->formatSpellout(21, array(), array(), array(), 'en_US');
// outputs: "twenty-one"
```

### formatDuration

string *public* *formatDuration*(mixed *value*[, array *attributes*[, array *textAttributes*[, array *symbols*[, string *locale*]]]])

Formats duration number. If locale is not specified default one will be used.

```
echo $numberFormatter->formatDuration(3661);
// outputs: "1:01:01"

echo $numberFormatter->formatDuration(
    3661,
    array(),
    array(\NumberFormatter::DEFAULT_RULESET => "%with-words"),
    array(),
    'en_US'
);
// outputs: "1 hour, 1 minute, 1 second"
```

### formatOrdinal

string *public* *formatOrdinal*(mixed *value*[, array *attributes*[, array *textAttributes*[, array *symbols*[, string *locale*]]]])

Formats ordinal number. If locale is not specified default one will be used.

```
echo $numberFormatter->formatDuration(1);
// outputs: "1st"

echo $numberFormatter->formatDuration(3, array(), array(), array(), 'en_US');
// outputs: "3rd"
```

### getAttribute

int *public* *getAttribute*(string|int *attribute*[, string|int *style*[, string *locale*]])

Gets numeric attribute of intl NumberFormatter related to passed locale. If locale is not passed, default one will be used.

```
echo $numberFormatter->getAttribute('parse_int_only', 'decimal', 'en_US');
// outputs: 0

echo $numberFormatter->getAttribute(\NumberFormatter::MAX_INTEGER_DIGITS, \NumberFormatter::DECIMAL, 'en_US');
// outputs: 309
```

### getTextAttribute

string *public* *getTextAttribute*(string|int *textAttribute*[, string|int *style*[, string *locale*]])

Gets text attribute of intl NumberFormatter related to passed locale. If locale is not passed, default one will be used.

```
echo $numberFormatter->getTextAttribute('negative_prefix', 'decimal', 'en_US');
// outputs: "-"

echo $numberFormatter->getTextAttribute(\NumberFormatter::\NEGATIVE_PREFIX', \NumberFormatter::CURRENCY, 'en_US');
// outputs: "($"
```

### getSymbol

string *public* *getSymbol*(string|int *symbol*[, string|int *style*[, string *locale*]])

Gets symbol of intl NumberFormatter related to passed locale. If locale is not passed, default one will be used.

```
echo $numberFormatter->getSymbol('DECIMAL_SEPARATOR_SYMBOL', 'DECIMAL', 'en_US');
// outputs: "."

echo $numberFormatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, \NumberFormatter::DECIMAL, 'en_US');
// outputs: ","
```

Twig
====

Filters
-------

Next filters are available in Twig templates:

###oro_format_number

This filter formats a number to localized format according to passed number style and optional custom options:

Simple usage of this filter requires a style of number. Next values can be used: 'decimal', 'currency', 'percent',
'scientific', 'spellout', 'ordinal', 'duration'.

This example outputs a string in localized format like this: 10,000.000
```
{{ 10000|oro_format_number('decimal') }}
```

This example outputs MINUS 10.0000,123 and shows what options could be passed to customize format.
```
{{ -100000.123|oro_format_number('decimal', {
    attributes: {'grouping_size': 4},
    textAttributes: {'negative_prefix': 'MINUS'},
    symbols: {'decimal_separator_symbol': ',', 'grouping_separator_symbol': '.'},
    locale: 'en_US'
}) }}
```

###oro_format_currency

###oro_format_decimal

###oro_format_percent

###oro_format_spellout

###oro_format_duration

###oro_format_ordinal

Functions
---------

Next functions are available in Twig templates:

### oro_locale_number_attribute

### oro_locale_number_text_attribute

### oro_locale_number_symbol


JS
==
