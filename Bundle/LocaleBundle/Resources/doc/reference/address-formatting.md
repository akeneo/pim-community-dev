Address Formatting
==================

Table of Contents
-----------------
 - [Formats source]
 - [PHP Address Formatter](#php-address-formatter)
    - [Methods and examples of usage](#methods-and-examples-of-usage)
      - [format](#format)
      - [getAddressFormat](#getAddressFormat)
   - [Twig](#twig)
    - [Filters](#filters)
      - [oro_format_address](#oro_format_address)
   - [JS](#js)
    - [Methods and examples of usage](#js_methods-and-examples-of-usage)
        - [format](#js_format)
        - [getAddressFormat](#js_getAddressFormat)

Formats source
================
Address formats may be found in address_format.yml. Address formats are grouped by country.

Example of format configuration for US:

```yaml
US:
    format: '%name%\n%organization%\n%street%\n%CITY% %REGION_CODE% %COUNTRY_ISO2% %postal_code%'
```

Possible format placeholders:

* *name* - address owner name
* *organization* - organization
* *street* - street
* *street2* - street line 2
* *city* - city
* *country* - country name
* *country_iso2* - country ISO2 code
* *country_iso3* - country ISO3 code
* *postal_code* - postal/ZIP code
* *region* - region
* *region_code* - region code

In case when format placeholder is in upper case corresponding value will be also upper cased.
Address formatter uses name formatter to format names according to address country locale.

In address_format.yml stored also additional optional data that does not used for now in LocaleBundle. Possible keys:

* *latin_format* - address format for latin characters
* *require* - array of required address fields for country
* *region_name_type* - how region named in country
* *zip_name_type* - how post code (zip) named in country
* *format_charset* - format charset encoding
* *postprefix* - post code prefix

PHP Address Formatter
====================

**Class:** Oro\Bundle\LocaleBundle\Formatter\AddressFormatter

**Service id:** oro_locale.formatter.address

Formats addresses based on given country address format. By default address country used for formatting.

Methods and examples of usage
-----------------------------

### format

string *public* *format*(AddressInterface *address*[, string *country*[, string *newLineSeparator*]])

This method can be used to format objects that implements AddressInterface.
To format address using specific country format *country* parameters may be set.
*newLineSeparator* parameter defines default line separator as \n and also can be changed.

```php
$formatter = $container->get('oro_locale.formatter.address');
// $region->getCode() is CA
// $country->getIso2Code() is US
$region->setCountry($country);
$address = new Address();
$address->setStreet('726 N. Vista Street');
$address->setCity('Los Angeles');
$address->setRegion($region);
$address->setPostalCode('90046');
$address->setOrganization('Oro Inc.');
$address->setCountry($country);
echo $formatter->format($address);
```

Outputs:

```
Oro Inc.
726 N. Vista Street
LOS ANGELES CA US 90046
```


### getAddressFormat

string *public* *getAddressFormat*([string *localeOrRegion*])

Get address format based on locale or region, if argument is not passed locale from system configuration will be used.

Twig
====

Filters
-------

### oro_format_address

This filter use *format* method from address formatter, and has same logic.
By default new line separator set to *&lt;br/&gt;*

```
{{ address|oro_format_address('US') }}
```

JS
============

Methods and examples of usage
-----------------------------

### format

string *public* *format*(Object *address*[, String *country*[, String|Function *newLine*]])

This method can be used to format addresses.
To format address using specific country format *country* parameters may be set.
*newLine* parameter defines default line separator. newLine may be a string which will be used as line separator or
function which will be called for each line and which must return string.

Possible address object parameters are:
* *prefix* - name prefix
* *suffix* - name suffix
* *first_name* - first name
* *middle_name* - middle name
* *last_name* - last name
* *organization* - organization
* *street* - street
* *street2* - street line 2
* *city* - city
* *country* - country name
* *country_iso2* - country ISO2 code
* *country_iso3* - country ISO3 code
* *postal_code* - postal/ZIP code
* *region* - region
* *region_code* - region code

Example:

```javascript
require(['oro/formatter/address'],
function(addressFormatter) {
    var data = this.model.toJSON();
    data.formatted_address = addressFormatter.format({
        prefix: data.namePrefix,
        suffix: data.nameSuffix,
        first_name: data.firstName,
        middle_name: data.middleName,
        last_name: data.lastName,
        organization: data.organization,
        street: data.street,
        street2: data.street2,
        city: data.city,
        country: data.country,
        country_iso2: data.countryIso2,
        country_iso3: data.countryIso3,
        postal_code: data.postalCode,
        region: data.region,
        region_code: data.regionCode
    });
});
```

### getAddressFormat

string *public* *getAddressFormat*([string *country*])

Get address format based on country, if argument is not passed default country from system configuration will be used.