OroLocaleBundle
===============

This bundle provides next localization tools:
- numbers and datetime formatting (intl is used)
- person names and postal addresses formatting
- dictionary of currencies, phone prefixes and default locales of countries

Locale Settings
---------------

Provides such locale settings of application as:

* person names formats
* addresses formats
* calendar
* time zone
* location
* currency specific data
  * currency symbols based on currency codes
  * currency code, phone prefix, default locale based on country

Uses system configuration as a source of settings.

See detailed [documentation](./Resources/doc/reference/locale-settings.md) for more details.

Number Formatting
-----------------

Includes tools for localized formatting numbers:
* [PHP side formatter](./Resources/doc/reference/number-formatting.md#php-number-formatter)
* [Twig functions and filters](./Resources/doc/reference/number-formatting.md#twig)
* [JS side formatter](./Resources/doc/reference/number-formatting.md#js)

See detailed [documentation](./Resources/doc/reference/number-formatting.md) for more details.

Date and Time Formatting
------------------------

Includes tools for localized formatting of date and time values:
* [PHP DateTime Formatter](./Resources/doc/reference/datetime-formatting.md#php-datetime-formatter))

Person Names Formatting
-----------------------

Postal Addresses Formatting
---------------------------

Please see [documentation](./Resources/doc/index.md) for more details.
