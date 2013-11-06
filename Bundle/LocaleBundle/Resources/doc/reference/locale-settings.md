Locale Settings
===============

Table of Contents
-----------------
 - [Overview](#overview)
 - [Locale](#locale)
 - [Calendar](#calendar)
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
* list addresses formats
* instance of calendar object to get localized calendar data
* currency specific data
  * currency symbols based on currency codes
  * currency code, phone prefix, default locale based on country

Locale
======

Locale settings can provide default application locale. This setting is based on system configuration and can be
different per user.

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

Calendar
========

Locale settings can provide instance of localized Calendar class (Oro\Bundle\LocaleBundle\Model\Calendar). This class
can be used to get localized calendar data based on application locale and application language.


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
en: %prefix% %first_name% %middle_name% %last_name% %suffix%
en_US: %prefix% %first_name% %middle_name% %last_name% %suffix%
ru: %last_name% %first_name% %middle_name%
ru_RU: %last_name% %first_name% %middle_name%
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
