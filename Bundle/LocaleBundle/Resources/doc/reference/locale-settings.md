Locale Settings
===============

Table of Contents
-----------------
 - [Overview](#overview)
 - [Locale](#locale)
 - [Language](#language)
 - [Calendar](#calendar)
   - [First day of week](#first-day-of-week)
   - [Month names](#month-names)
   - [Day of week names](#day-of-week-names)
 - [Location](#location)
 - [Time Zone](#time-zone)
 - [Currencies](#currencies)
 - [Names formats](#names-formats)
 - [Addresses formats](#addresses-formats)

Overview
========

Locale Settings is a service of Oro\Bundle\LocaleBundle\Model\LocaleSettings class. Id of the service is "oro_locale.settings".
This service can be used to get locale specific settings of the application such as:
* locale
* language
* location
* calendar
* time zone
* list of person names formats
* list of addresses formats
* currency specific data
  * currency symbols based on currency codes
  * currency code, phone prefix, default locale based on country

Locale
======

Locale settings can provide default application locale. This setting is based on system configuration and can be
different per user. Locale is used by all formatters, such as for names, addresses, numbers, date and times.

Example of getting current locale:

```php
$localeSettings = $this->get('oro_locale.settings');
$locale = $locale->getLocale();
```

Locale Settings class also provides help static methods related to locales:

**Oro\Bundle\LocaleBundle\Model\LocaleSettings::getValidLocale**

Validates given locale according to real data of environment. The purpose of this method to ensure that locale is
valid in current environment (PHP intl extension, ICU version). If locale is not supported than fallback valid default
one will be used. This method also try to strip all parts of locale different from \Locale::LANG_TAG,
\Locale::SCRIPT_TAG and \Locale::REGION_TAG.

Example of usage:
```php
// outputs ru_RU
echo \Oro\Bundle\LocaleBundle\Model\LocaleSettings::getValidLocale('ru_RU');

// outputs en_US
echo \Oro\Bundle\LocaleBundle\Model\LocaleSettings::getValidLocale('en_Hans_CN_nedis_rozaj_x_prv1_prv2');

// outputs en_US if this is a default locale
echo \Oro\Bundle\LocaleBundle\Model\LocaleSettings::getValidLocale('unknown');
```

**Oro\Bundle\LocaleBundle\Model\LocaleSettings::getLocales**

Returns the list of all available locales.

**Oro\Bundle\LocaleBundle\Model\LocaleSettings::getCountryByLocale**

Gets country by locale. If could not find result than returns default country.

Language
========

Locale settings provides application language configuration. Application language affects translations and representation
of date times. For example you could have en_US locale but french language, in this case date/times will be localized
using en_US locale formats but with french language. To get current language there is a corresponding method:

```php
$localeSettings = $this->get('oro_locale.settings');
$language = $locale->getLanguage();
```

Calendar
========

Locale settings can provide instance of localized Calendar class (Oro\Bundle\LocaleBundle\Model\Calendar). This class
can be used to get localized calendar data based on application locale and application language.

Example of getting calendar from locale settings:

```php
$localeSettings = $this->get('oro_locale.settings');
$calendar = $locale->getCalendar();
```

Calendar provides next information:

### First day of week

First day of week depends from locale in Locale Settings.

```php
// Returns one of constants of Calendar: DOW_SUNDAY, DOW_MONDAY, DOW_TUESDAY, DOW_WEDNESDAY, DOW_THURSDAY, DOW_FRIDAY, DOW_SATURDAY
$firstDayOfWeek = $calendar->getFirstDayOfWeek();
```

### Month names

Month names depends from application language in Locale Settings.

```php
// array(
//   1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July',
//   'August', 'September', 'October', 'November', 'December',
// )
$wideMonthNames = $calendar->getMonthNames();
$wideMonthNames = $calendar->getMonthNames(Calendar::WIDTH_WIDE);

// array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec')
$abbreviatedMonthNames = $calendar->getMonthNames(Calendar::WIDTH_ABBREVIATED);

$shortMonthNames = $calendar->getMonthNames(Calendar::WIDTH_SHORT);

// array(1 => 'J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D')
$narrowMonthNames = $calendar->getMonthNames(Calendar::WIDTH_NARROW);
```

### Day of week names

Day of week names depends from application language in Locale Settings.

```php
// array(
//   Calendar::DOW_SUNDAY    => 'Sunday',
//   Calendar::DOW_MONDAY    => 'Monday',
//   Calendar::DOW_TUESDAY   => 'Tuesday',
//   Calendar::DOW_WEDNESDAY => 'Wednesday',
//   Calendar::DOW_THURSDAY  => 'Thursday',
//   Calendar::DOW_FRIDAY    => 'Friday',
//   Calendar::DOW_SATURDAY  => 'Saturday',
// );
$wideDowNames = $calendar->getDayOfWeekNames();
$wideDowNames = $calendar->getDayOfWeekNames(Calendar::WIDTH_WIDE);

// array(
//   Calendar::DOW_SUNDAY    => 'Sun',
//   Calendar::DOW_MONDAY    => 'Mon',
//   Calendar::DOW_TUESDAY   => 'Tue',
//   Calendar::DOW_WEDNESDAY => 'Wed',
//   Calendar::DOW_THURSDAY  => 'Thu',
//   Calendar::DOW_FRIDAY    => 'Fri',
//   Calendar::DOW_SATURDAY  => 'Sat',
// );
$abbreviatedDowNames = $calendar->getDayOfWeekNames(Calendar::WIDTH_ABBREVIATED);
$shortDowNames = $calendar->getDayOfWeekNames(Calendar::WIDTH_SHORT);

// array(
//   Calendar::DOW_SUNDAY    => 'S',
//   Calendar::DOW_MONDAY    => 'M',
//   Calendar::DOW_TUESDAY   => 'T',
//   Calendar::DOW_WEDNESDAY => 'W',
//   Calendar::DOW_THURSDAY  => 'T',
//   Calendar::DOW_FRIDAY    => 'F',
//   Calendar::DOW_SATURDAY  => 'S',
// );
$narrowDowNames = $calendar->getDayOfWeekNames(Calendar::WIDTH_NARROW);
```

Location
========

Location is a country associated with locale settings. Locations affects formatting of addresses in mode when
addresses are not formatted using their Countries.

Example of getting country location from locale settings:
```php
$localeSettings = $this->get('oro_locale.settings');
// US or some other code of the country
$country = $locale->getCountry();
```

Additional locale data is available in Locale Settings. Using this data based on country next information could be
accessed:
* currency code
* phone prefix
* default locale

This data is loaded from bundle's file ./Resources/config/locale_data.yml. Other bundles could provide their files
to extend this data. Example of locale_data.yml file:

```yml
AD:
    currency_code: EUR
    phone_prefix: '376'
    default_locale: ca
AE:
    currency_code: AED
    phone_prefix: '971'
    default_locale: ar_AE
```

Time Zone
=========

All dates in application are stored in UTC time zone. When dates are displayed on the UI they are formatted via date/time
formatter. This formatter uses time zone setting from Locale Settings to display date times with respect of time zone.

List of available timezones in PHP: http://php.net/manual/en/timezones.php

Example of getting time zone from Locale settings:

```php

$localeSettings = $this->get('oro_locale.settings');
// America/Los_Angeles or some other time zone
$timeZone = $locale->getTimeZone();
```

Currencies
==========

Locale Settings stores default currency of application. [Number formatter](./number-formatting.md) uses it for
formatting when currency is not specified.

Example of getting currency from Locale Settings:

```php
$localeSettings = $this->get('oro_locale.settings');
// USD or some other currency code
$currency = $localeSettings->getCurrency();
```

Example of getting currency symbol by currency code:

```php
$localeSettings = $this->get('oro_locale.settings');
// $
$symbol = $localeSettings->getCurrencySymbolByCurrency('USD');
```

Data about currency code and currency symbols are loaded from bundle's file ./Resources/config/currency_data.yml. Other bundles could provide their files
to extend this data.

Example of currency_data.yml file:

```yml
UAH:
    symbol: ₴
UGX:
    symbol: UGX
USD:
    symbol: $
```


Names formats
=============

This data is used by [name formatter](./name-formatting.md). Locale settings can gets the full list of name formats
that are available:

```php
$localeSettings = $this->get('oro_locale.settings');
$nameFormats = $localeSettings->getNameFormats();
```

Name formats are loaded from bundle's file ./Resources/config/name_format.yml. Other bundles could provide their files
to extend name formats configuration.

Example of name_format.yml file:

```yml
en: '%prefix% %first_name% %middle_name% %last_name% %suffix%'
en_US: %prefix% %first_name% %middle_name% %last_name% %suffix%'
ru: '%last_name% %first_name% %middle_name%'
ru_RU: '%last_name% %first_name% %middle_name%'
```

See name formats [detailed documentation](./name-formatting.md).


Addresses formats
=================

This data is used by [address formatter](./address-formatting.md). Locale settings can gets the full list of addresses
formats that are available:

```php
$localeSettings = $this->get('oro_locale.settings');
$addressesFormats = $localeSettings->getAddressFormats();
```

Addresses formats are loaded from bundle's file ./Resources/config/address_format.yml. Other bundles could provide
their files to extend address formats configuration.

Example of address_format.yml file:

```yml
AD:
    format: '%name%\n%organization%\n%street%\n%postal_code% %REGION%\n%COUNTRY%'
    require: [street, region]
    region_name_type: parish
AE:
    format: '%name%\n%organization%\n%street%\n%city%\n%country%'
    require: [street, city]
AG:
    require: [street]
AM:
    format: '%name%\n%organization%\n%street%\n%postal_code%\n%city%\n%region%\n%country%'
    latin_format: '%name%\n%organization%\n%street%\n%postal_code%\n%city%\n%region%\n%country%'
```

See address formats [detailed documentation](./address-formatting.md).
