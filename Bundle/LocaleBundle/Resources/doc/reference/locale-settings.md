Locale Settings
===============

Table of Contents
-----------------
 - [Overview](#overview)
 - [Names formats](#names-formats)

Overview
========

Locale Settings is a service of Oro\Bundle\LocaleBundle\Model\LocaleSettings class. Id of the service is "oro_locale.settings".
This service can be used to get locale specific settings of the application such as:
* list of person names formats
* list addresses formats
* instance of calendar object to get localized calendar data
* time zone
* location
* currency specific data
  * currency symbols based on currency codes
  * currency code, phone prefix, default locale based on country

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
