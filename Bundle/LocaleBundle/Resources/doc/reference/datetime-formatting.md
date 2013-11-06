Date and Datetime Formatting
============================

Table of Contents
-----------------
  - [PHP DateTime Formatter](#php-datetime-formatter)
    - [format](#format)
    - [formatDate](#formatdate)
    - [formatTime](#formattime)
    - [getPattern](#getpattern)
  - [PHP DateTime Format Converters](#php-datetime-format-converters)
    - [getDateFormat](#getdateformat)
    - [getTimeFormat](#gettimeformat)
    - [getDateTimeFormat](#getdatetimeformat)
  - [Twig Extensions](#twig)
  - [JS DateTime Formatter](#js)


PHP DateTime Formatter
----------------------

DateTime formatter provides methods that allow formatting of entered data.
Formatter uses standard Intl library format types to render values:

* \IntlDateFormatter::NONE;
* \IntlDateFormatter::SHORT;
* \IntlDateFormatter::MEDIUM;
* \IntlDateFormatter::LONG;
* \IntlDateFormatter::FULL.

Each format uses it's own localized format for date, time and datetime.
Default date format is \IntlDateFormatter::MEDIUM, default time format is \IntlDateFormatter::SHORT.

### format

**Signature:** format(\DateTime|string|int date, dateType = null, timeType = null, locale = null, timeZone = null, pattern = null)

This functions provides most basic functionality to format date and time values.
It allows setting of date and time format types from Intl library, current locale as a string,
current timezone as a string and custom format pattern (in this case date and time format types will not be used).

```php
echo $formatter->format(new \DateTime('2012-12-12 23:23:23'));
// Dec 13, 2012 12:23 AM

echo $formatter->format(new \DateTime('2012-12-12 23:23:23'), \IntlDateFormatter::FULL, \IntlDateFormatter::MEDIUM);
// Thursday, December 13, 2012 12:23:23 AM

echo $formatter->format(new \DateTime('2012-12-12 23:23:23'), null, null, 'ru');
// 13.12.2012 0:23

echo $formatter->format(new \DateTime('2012-12-12 23:23:23'), null, null, null, 'America/Los_Angeles');
// Dec 12, 2012 1:23 PM

echo $formatter->format(new \DateTime('2012-12-12 23:23:23'), null, null, null, null, 'yyyy-MM-dd|HH:mm:ss');
// 2012-12-13|00:23:23
```

### formatDate

**Signature:** formatDate(\DateTime|string|int date, dateType = null, locale = null, timeZone = null)

This function formats date value using _format_ function described above.
Receives date value, date format type from Intl library, current locale and current timezone.

```php
echo $formatter->formatDate(new \DateTime('2012-12-12 23:23:23'));
// Dec 13, 2012

echo $formatter->formatDate(new \DateTime('2012-12-12 23:23:23'), \IntlDateFormatter::FULL);
// Thursday, December 13, 2012

echo $formatter->formatDate(new \DateTime('2012-12-12 23:23:23'), null, 'ru');
// 13.12.2012

echo $formatter->formatDate(new \DateTime('2012-12-12 23:23:23'), null, null, 'America/Toronto');
// Dec 12, 2012
```

### formatTime

**Signature:** formatTime(\DateTime|string|int date, dateType = null, locale = null, timeZone = null)

This function formats time value using _format_ function described above.
Receives time value, time format type from Intl library, current locale and current timezone.

```php
echo $formatter->formatTime(new \DateTime('2012-12-12 23:23:23'));
// 12:23 AM

echo $formatter->formatTime(new \DateTime('2012-12-12 23:23:23'), \IntlDateFormatter::FULL);
// 12:23:23 AM GMT+02:00

echo $formatter->formatTime(new \DateTime('2012-12-12 23:23:23'), null, 'ru');
// 0:23

echo $formatter->formatTime(new \DateTime('2012-12-12 23:23:23'), null, null, 'America/Toronto');
// 4:23 PM
```

### getPattern

**Signature:** getPattern($dateType, $timeType, $locale = null)

Returns Intl library pattern for specified date and time format types and locale.

```php
echo $formatter->getPattern(\IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
// EEEE, MMMM d, y h:mm:ss a zzzz

echo $formatter->getPattern(\IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'ru');
// EEEE, d MMMM y 'г'. H:mm:ss zzzz
```


PHP DateTime Format Converters
------------------------------

OroPlatform application contains several libraries that works with datetime values.
Each library has its own datetime format placeholders, so, to unify approach to generate localized format strings
for all libraries LocaleBundle provides format converters.

For each used library there must be a format converter that contains rules of converting
of standard internal format to specific library format. Intl library format is used
for internal format representation. Each format converter has as alias specified as an alias in service configuration
and used to extract it from registry.

Main entry point for developer is a converter registry (DateTimeFormatConverterRegistry) -
it simply collects and stores existing format converters and allows to receive appropriate converter by it's alias.

LocaleBundle contains following format converters:
 - intl (IntlDateTimeFormatConverter) - default format converter that simply returns Intl formats;
 - moment (MomentDateTimeFormatConverter) - format converter for moment.js library.

Also bundle contains interface DateTimeFormatConverterInterface that must be implemented by all format converters.
Here is list of interface functions.

### getDateFormat

**Signature:** getDateFormat(dateFormat = null, locale = null)

Returns localized date format for specific library. Optionally receives date format type form Intl library
and custom locale.

```php
echo $converterRegistry->getFormatConverter('intl')->getDateFormat();
echo $converterRegistry->getFormatConverter('moment')->getDateFormat();
// MMM d, y
// MMM D, YYYY

echo $converterRegistry->getFormatConverter('intl')->getDateFormat(\IntlDateFormatter::FULL, 'ru');
echo $converterRegistry->getFormatConverter('moment')->getDateFormat(\IntlDateFormatter::FULL, 'ru');
// EEEE, d MMMM y 'г'.
// dddd, D MMMM YYYY [г].
```

### getTimeFormat

**Signature:** getTimeFormat(timeFormat = null, locale = null)

Returns localized time format for specific library. Optionally receives time format type form Intl library
and custom locale.

```php
echo $converterRegistry->getFormatConverter('intl')->getTimeFormat();
echo $converterRegistry->getFormatConverter('moment')->getTimeFormat();
// h:mm a
// h:mm A

echo $converterRegistry->getFormatConverter('intl')->getTimeFormat(\IntlDateFormatter::MEDIUM, 'ru');
echo $converterRegistry->getFormatConverter('moment')->getTimeFormat(\IntlDateFormatter::MEDIUM, 'ru');
// H:mm:ss
// H:mm:ss
```

### getDateTimeFormat

**Signature:** getDateTimeFormat(dateFormat = null, timeFormat = null, locale = null)

Returns localized datetime format for specific library. Optionally receives date and time format types
form Intl library and custom locale.

```php
echo $converterRegistry->getFormatConverter('intl')->getDateTimeFormat();
echo $converterRegistry->getFormatConverter('moment')->getDateTimeFormat();
// MMM d, y h:mm a
// MMM D, YYYY h:mm A

echo $converterRegistry->getFormatConverter('intl')->getDateTimeFormat(
    \IntlDateFormatter::FULL,
    \IntlDateFormatter::MEDIUM,
    'ru'
);
echo $converterRegistry->getFormatConverter('moment')->getDateTimeFormat(
    \IntlDateFormatter::FULL,
    \IntlDateFormatter::MEDIUM,
    'ru'
);
// EEEE, d MMMM y 'г'. H:mm:ss
// dddd, D MMMM YYYY [г]. H:mm:ss
```




