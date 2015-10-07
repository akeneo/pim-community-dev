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
  - [Twig Extensions](#twig-extensions)
    - [Formatter filters](#formatter-filters)
    - [Format Converter functions](#format-converter-functions)
  - [JS DateTime Formatter](#js-datetime-formatter)


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

Twig Extensions
---------------

LocaleBundle has two twig extensions that provides formatter filters and format converter functions.

### Formatter filters

Twig extension DateTimeExtension has following functions.

#### oro_format_date

Proxy for [formatDate](#formatdate) function of DateTimeFormatter, receives date value as a first argument
and array of options as a second argument. Allowed options:
  * dateType,
  * locale,
  * timezone.

```
{{ entity.lastLogin|oro_format_date }}
{# Nov 6, 2013 #}

{{ entity.lastLogin|oro_format_date({'locale': 'ru'}) }}
{# 06.11.2013 #}
```

#### oro_format_time

Proxy for [formatTime](#formattime) function of DateTimeFormatter, receives time value as a first argument
and array of options as a second argument. Allowed options:
  * timeType,
  * locale,
  * timezone.

```
{{ entity.lastLogin|oro_format_time }}
{# 7:44 PM #}

{{ entity.lastLogin|oro_format_time({'locale': 'ru'}) }}
{# 19:44 #}
```

#### oro_format_datetime

Proxy for [format](#format) function of DateTimeFormatter, receives datetime value as a first argument
and array of options as a second argument. Allowed options:
  * dateType,
  * timeType,
  * locale,
  * timezone.

```
{{ entity.lastLogin|oro_format_datetime }}
{# Nov 6, 2013 7:44 PM #}

{{ entity.lastLogin|oro_format_datetime({'locale': 'ru'}) }}
{# 06.11.2013 19:44 #}
```

### Format Converter functions

Twig extension DateFormatExtension has following functions.

#### oro_date_format

Receives format converter alias, date format type and custom locale, returns date format from appropriate
format converter.

```
{{ oro_date_format('moment') }}
{# MMM D, YYYY #}

{{ oro_date_format('moment', null, 'ru') }}
{# DD.MM.YYYY #}
```

#### oro_time_format

Receives format converter alias, time format type and custom locale, returns time format from appropriate
format converter.

```
{{ oro_time_format('moment') }}
{# h:mm A #}

{{ oro_time_format('moment', null, 'ru') }}
{# H:mm #}
```

#### oro_datetime_format

Receives format converter alias, date and time format types and custom locale, returns time format from appropriate
format converter.

```
{{ oro_datetime_format('moment') }}
{# MMM D, YYYY h:mm A #}

{{ oro_datetime_format('moment', null, null, 'ru') }}
{# DD.MM.YYYY H:mm #}
```

#### oro_datetime_formatter_list

Returns array of all registered format converter aliases.

```
{{ oro_datetime_formatter_list()|join(', ') }}
{# intl, moment, jquery_ui, fullcalendar #}
```

JS DateTime Formatter
---------------------

From the frontend side there is JavaScript datetime converter that provides functions to format datetime values.
Formatter uses library moment.js to work with datetime values and localized formats injected from locale settings
configuration.

Formatter work with two string representations of datetime values: frontend - it's localized format
in current timezone, and backend - ISO format data in UTC (for date) or with direct timezone specification
(for datetime).

Formatter provides following functions.

### getDateFormat(), getTimeFormat(), getDateTimeFormat()

Returns appropriate localized frontend format for moment.js library received from locale settings configuration.

```js
console.log(datetimeFormatter.getDateTimeFormat());
// MMM D, YYYY h:mm A
```

### isDateValid(value), isTimeValid(value), isDateTimeValid(value)

Check whether input value has valid format and can be parsed to internal date representation.

```js
console.log(datetimeFormatter.isDateValid('qwerty'));
// false

console.log(datetimeFormatter.isDateTimeValid('oct 12 2013 12:12 pm'));
// true
```

### formatDate(value), formatTime(value), formatDateTime(value)

Receives either Date object or valid ISO string and returns value string in localized format.
Throws an exception in case of not valid string.

```js
console.log(datetimeFormatter.formatDate('2013-12-12'));
// Dec 12, 2013

console.log(datetimeFormatter.formatDateTime(new Date()));
// Nov 6, 2013 7:32 PM
```

### convertDateToBackendFormat(value), convertTimeToBackendFormat(value), convertDateTimeToBackendFormat(value, timezoneOffset)

Receives localized string data and convert in to ISO format string, *convertDateTimeToBackendFormat* optionally can receive
timezone offset - if no offset is set default offset will be used.
Throws an exception in case of not valid string.

```js
console.log(datetimeFormatter.convertDateToBackendFormat('Dec 12, 2013'));
// 2013-12-12

console.log(datetimeFormatter.convertDateTimeToBackendFormat('Nov 6, 2013 7:32 PM'));
// 2013-11-06T19:32:00+0200
```

### getMomentForBackendDate(value), getMomentForBackendTime(value), getMomentForBackendDateTime(value)

Receives either Date object or valid ISO string and returns moment object instance.
Throws an exception in case of not valid string.

### getMomentForFrontendDate(value), getMomentForFrontendTime(value), getMomentForFrontendDateTime(value[, timezoneOffset])

Receives valid formatted string and returns moment object instance.
Throws an exception in case of not valid string.

### unformatDate(value), unformatTime(value), unformatDateTime(value[, timezoneOffset])

Receives valid formatted string and returns Date object instance.
Throws an exception in case of not valid string.

### unformatBackendDate(value), unformatBackendTime(value), unformatBackendDateTime(value)

Receives either Date object or valid ISO string and returns Date object instance.
Throws an exception in case of not valid string.