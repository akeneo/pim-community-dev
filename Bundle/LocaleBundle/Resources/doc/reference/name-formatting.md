Name Formatting
==================

Table of Contents
-----------------
 - [Formats source](#format-source)
 - [PHP Name Formatter](#php-name-formatter)
    - [Methods and examples of usage](#methods-and-examples-of-usage)
      - [format](#format)
      - [getNameFormat](#getNameFormat)
   - [Twig](#twig)
    - [Filters](#filters)
      - [oro_format_name](#oro_format_name)
   - [JS](#js)
    - [Methods and examples of usage](#js_methods-and-examples-of-usage)
        - [format](#js_format)
        - [getNameFormat](#js_getNameFormat)

Formats source
================
Name formats may be found in name_format.yml. Name formats are grouped by locale.

Example of format configuration for en_US:

```yaml
en_US: %prefix% %first_name% %middle_name% %last_name% %suffix%
```

Possible format placeholders:

* *prefix* - name prefix
* *first_name* - first name
* *middle_name* - middle name
* *last_name* - last name
* *suffix* - name suffix

In case when format placeholder is in upper case corresponding value will be also upper cased.

PHP Name Formatter
====================

**Class:** Oro\Bundle\LocaleBundle\Formatter\NameFormatter

**Service id:** oro_locale.formatter.name

Formats name based on given locale.

Methods and examples of usage
-----------------------------

### format

string *public* *format*(*person*[, string *locale*])

This method can be used to format objects that implements one of next interfaces:

* *FirstNameInterface* - defines getter for first name
* *MiddleNameInterface* - defines getter for middle name
* *LastNameInterface* - defines getter for last name
* *NamePrefixInterface* - defines getter for name prefix
* *NameSuffixInterface* - defines getter for name suffix
* *FullNameInterface* - extends FirstNameInterface, MiddleNameInterface, LastNameInterface, NamePrefixInterface and NameSuffixInterface

To format name using specific locale format *locale* parameters may be passed.

Format:

```yaml
en_US: %prefix% %first_name% %middle_name% %LAST_NAME% %suffix%
```

Code:

```php
$formatter = $container->get('oro_locale.formatter.name');
// Person implements FullNameInterface
$person->setNamePrefix('Mr.');
$person->setFirstName('First');
$person->setMiddleName('Middle');
$person->setLastName('Last');
$person->setNameSuffix('Sn.');
echo $formatter->format($person, 'en_US');
```

Outputs:

```
Mr. First Middle LAST Sn.
```


### getNameFormat

string *public* *getNameFormat*([string *locale*])

Get name format based on locale, if argument is not passed locale from system configuration will be used.

Twig
====

Filters
-------

### oro_format_name

This filter use *format* method from name formatter, and has same logic.

```
{{ user|oro_format_name }}
```

JS
============

Methods and examples of usage
-----------------------------

### format

string *public* *format*(Object *person*[, String *locale*])

This method can be used to format names.
To format name using specific locale format *locale* parameters may be passed.

Possible name object parameters are same to format placeholder keys.

Usage example:

```javascript
require(['oro/formatter/name'],
function(nameFormatter) {
    var formattedName = nameFormatter.format({
        prefix: 'Mr.',
        first_name: 'First',
        middle_name: 'Middle',
        last_name: 'Last',
        suffix: 'Sn.'
    });
});
```

### getNameFormat

string *public* *getNameFormat*([string *locale*])

Get name format based on locale, if argument is not passed locale from system configuration will be used.
