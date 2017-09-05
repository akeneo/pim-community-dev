# Standard format

The standard format is a normalized array representation of the objects of the PIM. It it used to manipulate (query/update), describe or even sometimes store these objects *inside* the PIM. Currently it is *not* designed to provide a representation of these objects outside the PIM.

The standard format is consistent in term of:

* structure: for instance, products will always be represented the same way
* data formatting: for instance, dates will always be formatted the same way

The standard format always returns the complete structure, even if data is null.

## General points

Keys of the array are snake cased.

Boolean data are rendered as booleans (*true* or *false*). For instance, the standard format of an object that contains the property *a_boolean* would be:
    
        array:1 [
          "a_boolean" => false
        ]

Integer data are rendered as integers. For instance, the standard format of an object that contains the property *an_integer* would be:
    
        array:1 [
          "an_integer" => 42
        ]

Dates and datetimes are always strings formatted to [ISO-8601](https://en.wikipedia.org/wiki/ISO_8601), including the timezone.
For instance, the standard format of an object that contains the properties *a_datetime* and *a_date* would be:
    
        array:2 [
          "a_datetime" => "2016-06-23T11:24:44+02:00"
          "a_date" => "2016-06-23T00:00:00+04:00"
        ]


To avoid [loosing precision with floating points](http://floating-point-gui.de), and as [decimal type doesn't exist in PHP](http://php.net/manual/en/language.types.float.php), decimals are rendered as strings. If you need to perform precise operations on such numbers, please use [the arbitrary precision math functions](http://php.net/manual/en/ref.bc.php) or the [gmp functions](http://php.net/manual/en/ref.gmp.php).
For instance, the standard format of an object that contains the properties *a_decimal* and *a_negative_decimal* would be:
    
        array:2 [
          "a_decimal" => "46546.65987313"
          "a_negative_decimal" => "-45.8981226"
        ]

Linked entities are represented only by their identifier as strings. For instance, the standard format of a *foo* object that has a link to an external *bar* object would be:
    
        array:1 [
          "bar" => "the_bar_identifier"
        ]

## Flexible values

A flexible value is a data holder linked to an [attribute](#attribute). Depending on its attribute type, the value will be either a number, a string or even a more complex data type like a price collection or a metric for instance.

Flexible values are typically used in products and product models. They are accessible via the key *values*.

Those values can be localizable and/or scopable:

* *localizable* means its value depends on the locale
* *scopable* means its value depends on the scope (also called channel)
* *localizable and scopable* means its value depends on the locale and the scope (also called channel)

That's why a value always respect the following structure:

        array:3 [
          "locale" => "a locale code"
          "scope" => "a scope code"
          "data" => "the value for the given locale and scope"
        ]

And that's why, for the same attribute, it's possible to have multiple values:

        "a_localizable_attribute" => array:2 [
          0 => array:3 [
            "locale" => "en_US"
            "scope" => null
            "data" => "the data in English"
          ]
          1 => array:3 [
            "locale" => "fr_FR"
            "scope" => null
            "data" => "la donnée en français"
          ]
        ]

All types of attributes (except the *identifier* and *asset*) can be localizable and/or scopable. In the example above:
 
* there is a localizable image: *a_localizable_image*
* there is a scopable price: *a_scopable_price_with_decimal*
* there is a scopable and localizable text area: *a_localized_and_scopable_text_area*

Depending on the type of the value, the *data* key can have different structures:

| attribute type               	| data structure | data example                                                                                       | notes                                                                                                               |
| -----------------------------	| -------------- | -------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| identifier                   	| string         | `"foo"`                                                                                            |                                                                                                                     |
| file                         	| string         | `"f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt"`                                     | it represents the *key* of the object *Akeneo\Component\FileStorage\Model\FileInfoInterface*                        |
| image                        	| string         | `"f/4/d/1/f4d12ffbdbe628ba8e0b932c27f425130cc23535_imageA.jpg"`                                    | it represents the *key* of the object *Akeneo\Component\FileStorage\Model\FileInfoInterface*                        |
| date                         	| string         | `"2016-06-13T00:00:00+02:00"`                                                                      | formatted to ISO-8601 (see above)                                                                                   |
| multi select                 	| string[]       | `[0 => "optionA", 1 => "optionB"]`                                                                 | each element of the array represents the *code* of the *Pim\Component\Catalog\Model\AttributeOptionInterface*       |
| number                       	| string         | `"-99.8732"`                                                                                       | formatted as a string to avoid the floating point precision problem of PHP (see above)                              |
| reference data multi select  	| string[]       | `[0 => "fabricA",1 => "fabricB"]`                                                                  | each element of the array represents the *code* of the *Pim\Component\ReferenceData\Model\ReferenceDataInterface*   |
| simple select                	| string         | `"optionB"`                                                                                        | it represents the *code* of the *Pim\Component\Catalog\Model\AttributeOptionInterface*                              |
| reference data simple select 	| string         | `"colorB"`                                                                                         | it represents the *code* of the *Pim\Component\ReferenceData\Model\ReferenceDataInterface*                          |
| text                         	| string         | `"this is a text"`                                                                                 |                                                                                                                     |
| text area                    	| string         | `"this is a very very very very very long text"`                                                   |                                                                                                                     |
| yes/no                       	| boolean        | `true`                                                                                             |                                                                                                                     |
| metric                       	| array          | `["amount" => "987654321987.123456789123","unit" => "KILOWATT"]`                                   | *amount* and *unit* keys are expected *unit* should be a known unit depending on the metric family of the attribute |
| price collection             	| array          | `[0 => ["amount" => "45.00","currency" => "USD"], 1 => ["amount" => "56.53","currency" => "EUR"] ]`| *amount* and *currency* keys are expected for each price *currency* should be a known currency                      |


The following values data, that represents decimal values are represented with strings (when the `decimal_allowed` attribute property is set to false) in the standard format:

* metric (class *Pim\Component\Catalog\Model\MetricInterface*)
* price (class *Pim\Component\Catalog\Model\ProductPriceInterface*)
* number (class *Pim\Component\Catalog\Model\ValueInterface*)

When the `decimal_allowed` attribute property is set to true, they are represented with integers in the standard format.

Let's now consider a catalog with all attribute types possible and a *foo* entity, that contains an identifier and all the attributes of the catalog.

Its standard format would be the following:

        array:10 [
          "identifier" => "foo"
          "values" => array:19 [
            "sku" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "foo"
              ]
            ]
            "a_file" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt"
              ]
            ]
            "an_image" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "f/4/d/1/f4d12ffbdbe628ba8e0b932c27f425130cc23535_imageA.jpg"
              ]
            ]
            "a_date" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "2016-06-13T00:00:00+02:00"
              ]
            ]
            "a_multi_select" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  0 => "optionA"
                  1 => "optionB"
                ]
              ]
            ]
            "a_number_float" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "12.5678"
              ]
            ]
            "a_number_float_negative" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "-99.8732"
              ]
            ]
            "a_number_integer" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => 42
              ]
            ]
            "a_number_integer_negative" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => -5
              ]
            ]
            "a_ref_data_multi_select" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  0 => "fabricA"
                  1 => "fabricB"
                ]
              ]
            ]
            "a_ref_data_simple_select" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "colorB"
              ]
            ]
            "a_simple_select" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "optionB"
              ]
            ]
            "a_text" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "this is a text"
              ]
            ]
            "a_text_area" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "this is a very very very very very long text"
              ]
            ]
            "a_yes_no" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => true
              ]
            ]
            "a_localizable_image" => array:2 [
              0 => array:3 [
                "locale" => "en_US"
                "scope" => null
                "data" => "2/b/6/b/2b6b451334ee1a9aa83b5755590dae72ba254d8b_imageB_en_US.jpg"
              ]
              1 => array:3 [
                "locale" => "fr_FR"
                "scope" => null
                "data" => "d/e/3/f/de3f2a0af94d8b10ccc2c37bf4f945fd262d568e_imageB_fr_FR.jpg"
              ]
            ]
            "a_localized_and_scopable_text_area" => array:3 [
              0 => array:3 [
                "locale" => "en_US"
                "scope" => "ecommerce"
                "data" => "a text area for ecommerce in English"
              ]
              1 => array:3 [
                "locale" => "en_US"
                "scope" => "tablet"
                "data" => "a text area for tablets in English"
              ]
              2 => array:3 [
                "locale" => "fr_FR"
                "scope" => "tablet"
                "data" => "une zone de texte pour les tablettes en français"
              ]
            ]
            "a_metric" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  "amount" => "987654321987.123456789123"
                  "unit" => "KILOWATT"
                ]
              ]
            ]
            "a_metric_without_decimal" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  "amount" => 200
                  "unit" => "GRAM"
                ]
              ]
            ]
            "a_metric_negative" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  "amount" => "-20.000000000000"
                  "unit" => "CELSIUS"
                ]
              ]
            ]
            "a_metric_negative_without_decimal" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  "amount" => -100
                  "unit" => "CELSIUS"
                ]
              ]
            ]
            "a_price" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  0 => array:2 [
                    "amount" => "45.00"
                    "currency" => "USD"
                  ]
                  1 => array:2 [
                    "amount" => "-56.53"
                    "currency" => "EUR"
                  ]
                ]
              ]
            ]
            "a_scopable_price_without_decimal" => array:2 [
              0 => array:3 [
                "locale" => null
                "scope" => "ecommerce"
                "data" => array:2 [
                  0 => array:2 [
                    "amount" => 15
                    "currency" => "EUR"
                  ]
                  1 => array:2 [
                    "amount" => -20
                    "currency" => "USD"
                  ]
                ]
              ]
              1 => array:3 [
                "locale" => null
                "scope" => "tablet"
                "data" => array:2 [
                  0 => array:2 [
                    "amount" => 17
                    "currency" => "EUR"
                  ]
                  1 => array:2 [
                    "amount" => 24
                    "currency" => "USD"
                  ]
                ]
              ]
            ]
          ]
        ]


## Product

### Common structure

The products contains inner fields and product values that are linked to attributes.
All products have the same fields (identifier, label, family, groups, categories, associations, status, dates of creation and update) while product values are flexible among products. Its product values are provided via the key *values*.

Let's consider a *bar* product, without any product value, except its identifier *sku*. This product also contains:

* an identifier
* a family
* several groups
* several categories
* several associations related to groups and/or other products

Its standard format would be the following:
        
        array:10 [
          "identifier" => "bar"
          "family" => "familyA"
          "groups" => array:2 [
            0 => "groupA"
            1 => "groupB"
          ]
          "categories" => array:2 [
            0 => "categoryA"
            1 => "categoryB"
          ]
          "enabled" => false
          "values" => array:1 [
            "sku" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "bar"
              ]
            ]
          ]
          "created" => "2016-06-23T11:24:44+02:00"
          "updated" => "2016-06-23T11:24:44+02:00"
          "associations" => array:3 [
            "PACK" => array:1 [
              "groups" => []
              "products" => array:2 [
                0 => "foo"
                1 => "baz"
              ]
            ]
            "UPSELL" => array:1 [
              "groups" => array:1 [
                0 => "groupA"
              ]
              "products" => []
            ]
            "X_SELL" => array:2 [
              "groups" => array:1 [
                0 => "groupB"
              ]
              "products" => array:1 [
                0 => "foo"
              ]
            ]
          ]
        ]

| type          | data structure | data example                                                              | notes                                                                                            |
| ------------- | -------------- | ------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| identifier    | string         | `"bar"`                                                                   | it's the identifier of the product                                                               |
| family        | string         | `"familyA"`                                                               | it represents the *code* of the *Pim\Component\Catalog\Model\FamilyInterface*                    |
| groups        | array          | `[0 => "groupA", 1 => "groupB"]`                                          | it represents the *code* of the *Pim\Component\Catalog\Model\GroupInterface*                     |
| categories    | array          | `[0 => "categoryA", 1 => "categoryB"]`                                    | it represents the *code* of the object *Akeneo\Component\Classification\Model\CategoryInterface* |
| enabled       | boolean        | `true`                                                                    |                                                                                                  |
| values        | array          |                                                                           | see below                                                                                        |
| created       | string         | `"2016-06-13T00:00:00+02:00"`                                             | formatted to ISO-8601 (see above)                                                                |
| updated  	    | string         | `"2016-06-13T00:00:00+02:00"`                                             | formatted to ISO-8601 (see above)                                                                |
| associations  | array          | `["X_SELL" => ["groups" => [0 => "groupA"], "products" => [0 => "foo"]]]` | see below                                                                                        |


### Product associations

The structure of the array is composed as below:

        "associations" => array:3 [
          "X_SELL" => array:2 [
            "groups" => array:1 [
              0 => "groupB"
            ]
            "products" => array:1 [
              0 => "foo"
            ]
          ]
        ]

"X_SELL" represents the *code* of the *Pim\Component\Catalog\Model\AssociationTypeInterface*.

Each element in the array "groups" represents the *code* of the *Pim\Component\Catalog\Model\GroupInterface*

Each element in the array "products" represents the *identifier* of the *Pim\Component\Catalog\Model\ProductInterface*

If an association type does not contain neither element in groups, nor element in products, it is not returned.


### Product values

Let's now consider a catalog with all attribute types possible and a *foo* product, that contains:

* all the attributes of the catalog
* an identifier
* a family
* several groups
* several categories
* several associations related to groups and/or other products

Its standard format would be the following:

        array:10 [
          "identifier" => "foo"
          "family" => "familyA"
          "groups" => array:2 [
            0 => "groupA"
            1 => "groupB"
          ]
          "categories" => array:2 [
            0 => "categoryA1"
            1 => "categoryB"
          ]
          "enabled" => true
          "values" => array:19 [
            "sku" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "foo"
              ]
            ]
            "a_file" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt"
              ]
            ]
            "an_image" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "f/4/d/1/f4d12ffbdbe628ba8e0b932c27f425130cc23535_imageA.jpg"
              ]
            ]
            "a_date" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "2016-06-13T00:00:00+02:00"
              ]
            ]
            "a_multi_select" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  0 => "optionA"
                  1 => "optionB"
                ]
              ]
            ]
            "a_number_float" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "12.5678"
              ]
            ]
            "a_number_float_negative" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "-99.8732"
              ]
            ]
            "a_number_integer" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => 42
              ]
            ]
            "a_number_integer_negative" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => -5
              ]
            ]
            "a_ref_data_multi_select" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  0 => "fabricA"
                  1 => "fabricB"
                ]
              ]
            ]
            "a_ref_data_simple_select" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "colorB"
              ]
            ]
            "a_simple_select" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "optionB"
              ]
            ]
            "a_text" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "this is a text"
              ]
            ]
            "a_text_area" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => "this is a very very very very very long text"
              ]
            ]
            "a_yes_no" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => true
              ]
            ]
            "a_localizable_image" => array:2 [
              0 => array:3 [
                "locale" => "en_US"
                "scope" => null
                "data" => "2/b/6/b/2b6b451334ee1a9aa83b5755590dae72ba254d8b_imageB_en_US.jpg"
              ]
              1 => array:3 [
                "locale" => "fr_FR"
                "scope" => null
                "data" => "d/e/3/f/de3f2a0af94d8b10ccc2c37bf4f945fd262d568e_imageB_fr_FR.jpg"
              ]
            ]
            "a_localized_and_scopable_text_area" => array:3 [
              0 => array:3 [
                "locale" => "en_US"
                "scope" => "ecommerce"
                "data" => "a text area for ecommerce in English"
              ]
              1 => array:3 [
                "locale" => "en_US"
                "scope" => "tablet"
                "data" => "a text area for tablets in English"
              ]
              2 => array:3 [
                "locale" => "fr_FR"
                "scope" => "tablet"
                "data" => "une zone de texte pour les tablettes en français"
              ]
            ]
            "a_metric" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  "amount" => "987654321987.123456789123"
                  "unit" => "KILOWATT"
                ]
              ]
            ]
            "a_metric_without_decimal" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  "amount" => 200
                  "unit" => "GRAM"
                ]
              ]
            ]
            "a_metric_negative" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  "amount" => "-20.000000000000"
                  "unit" => "CELSIUS"
                ]
              ]
            ]
            "a_metric_negative_without_decimal" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  "amount" => -100
                  "unit" => "CELSIUS"
                ]
              ]
            ]
            "a_price" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  0 => array:2 [
                    "amount" => "45.00"
                    "currency" => "USD"
                  ]
                  1 => array:2 [
                    "amount" => "-56.53"
                    "currency" => "EUR"
                  ]
                ]
              ]
            ]
            "a_scopable_price_without_decimal" => array:2 [
              0 => array:3 [
                "locale" => null
                "scope" => "ecommerce"
                "data" => array:2 [
                  0 => array:2 [
                    "amount" => 15
                    "currency" => "EUR"
                  ]
                  1 => array:2 [
                    "amount" => -20
                    "currency" => "USD"
                  ]
                ]
              ]
              1 => array:3 [
                "locale" => null
                "scope" => "tablet"
                "data" => array:2 [
                  0 => array:2 [
                    "amount" => 17
                    "currency" => "EUR"
                  ]
                  1 => array:2 [
                    "amount" => 24
                    "currency" => "USD"
                  ]
                ]
              ]
            ]
          ]
          "created" => "2016-06-23T11:24:44+02:00"
          "updated" => "2016-06-23T11:24:44+02:00"
          "associations" => array:3 [
            "PACK" => array:1 [
              "groups" => []
              "products" => array:2 [
                0 => "bar"
                1 => "baz"
              ]
            ]
            "UPSELL" => array:1 [
              "groups" => array:1 [
                0 => "groupA"
              ]
              "products" => []
            ]
            "X_SELL" => array:2 [
              "groups" => array:1 [
                0 => "groupB"
              ]
              "products" => array:1 [
                0 => "bar"
              ]
            ]
          ]
        ]


## Other entities

### Attribute

        array:26 [
          "code" => "a_date"
          "type" => "pim_catalog_date"
          "labels" => array:2 [
            "en_US" => "A date"
            "fr_FR" => "Une date"
          ]
          "group" => "other"
          "unique" => false
          "useable_as_grid_filter" => false
          "allowed_extensions" => []
          "metric_family" => null
          "default_metric_unit" => null
          "reference_data_name" => null
          "available_locales" => array:1 [
            0 => "en_US"
          ]
          "max_characters" => null
          "validation_rule" => null
          "validation_regexp" => null
          "wysiwyg_enabled" => false
          "number_min" => null
          "number_max" => null
          "decimals_allowed" => false
          "negative_allowed" => false
          "date_min" => "2016-09-01T00:00:00+0200"
          "date_max" => "2016-09-30T00:00:00+0200"
          "max_file_size" => null
          "minimum_input_length" => 0
          "sort_order" => 0
          "localizable" => true
          "scopable" => false
        ]

| type                   | data structure | data example                       | notes                                                                                                                                                                                  |
| ---------------------- | -------------- | ---------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| code                   | string         | `"a_date"`                         | it's the identifier of the attribute                                                                                                                                                   |
| type                   | string         | `"pim_catalog_date"`               |                                                                                                                                                                                        |
| labels                 | string[]       | `[0 => "A date", 1 => "Une date"]` | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface*                                                                                       |
| group                  | string         | `"other"`                          | it represents the *code* of the object *Pim\Component\Catalog\Model\GroupInterface*                                                                                                    |
| unique                 | boolean        | `false`                            |                                                                                                                                                                                        |
| useable_as_grid_filter | boolean        | `true`                             |                                                                                                                                                                                        |
| allowed_extensions     | string[]       | `[0 => "pdf", 1 => "doc"]`         | List of extensions                                                                                                                                                                     |
| metric_family          | string         | `"Power"`                          | it represents the constant *FAMILY* in classes of *Akeneo/Bundle/MeasureBundle/Family/*                                                                                                |
| default_metric_unit    | string         | `"watt"`                           | it represents one of the constant in classes of *Akeneo/Bundle/MeasureBundle/Family/*, except *FAMILY*                                                                                 |
| reference_data_name    | string         | `"color"`                          | it represents the *code* of the object *Pim\Component\ReferenceData\Model\ReferenceDataInterface*                                                                                      |
| available_locales      | string[]       | `[0 => "en_US", 1 => "fr_FR"]`     | only for locale specific. If the array is empty, locale specific is false. Each element of the array represents the *code* of the object *Pim\Component\Catalog\Model\LocaleInterface* |
| max_characters         | integer        | `255`                              |                                                                                                                                                                                        |
| validation_rule        | string         | `"email"`                          |                                                                                                                                                                                        |
| validation_regexp      | string         | `"[0-9]"`                          |                                                                                                                                                                                        |
| wysiwyg_enabled        | boolean        | `false`                            |                                                                                                                                                                                        |
| number_min             | string         | `"10"`                             |                                                                                                                                                                                        |
| number_max             | string         | `"25"`                             |                                                                                                                                                                                        |
| decimals_allowed       | boolean        | `true`                             |                                                                                                                                                                                        |
| negative_allowed       | boolean        | `false`                            |                                                                                                                                                                                        |
| date_min               | string         | `"2016-09-01T00:00:00+0200"`       | formatted to ISO-8601 (see above)                                                                                                                                                      |
| date_max               | string         | `"2016-09-01T00:00:00+0200"`       | formatted to ISO-8601 (see above)                                                                                                                                                      |
| max_file_size          | string         | `"255.00"`                         | limit in MB                                                                                                                                                                            |
| minimum_input_length   | integer        | `2`                                |                                                                                                                                                                                        |
| sort_order             | integer        | `0`                                |                                                                                                                                                                                        |
| localizable            | boolean        | `false`                            |                                                                                                                                                                                        |
| scopable               | boolean        | `false`                            |                                                                                                                                                                                        |

#### Enterprise edition

On Enterprise edition, attribute is overridden to add:

        array:27 [
          ...
          "is_read_only" => false
        ]

| type         | data structure | data example |
| ------------ | -------------- | ------------ |
| is_read_only | boolean        | `true`       |


### Attribute Option

        array:4 [
          "code" => "option_a"
          "attribute" => "a_simple_select"
          "sort_order" => 1
          "labels" => array:1 [
            "en_US" => "A option"
          ]
        ]
        
type       | data structure | data example              | notes                                                                                            |
---------- | -------------- | ------------------------- | ------------------------------------------------------------------------------------------------ |
code       | string         | `"option_a"`              | it's the identifier of the attribute option                                                      |
attribute  | string         | `"a_simple_select"`       | the element represents the *code* of the *Pim\Component\Catalog\Model\AttributeInterface*        |
sort_order | integer        | `0`                       |                                                                                                  |
labels     | string[]       | `["en_US" => "A option"]` | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface* |


### Association Type

        array:2 [
          "code" => "X_SELL"
          "labels" => array:2 [
            "en_US" => "Cross sell"
            "fr_FR" => "Vente croisée"
          ]
        ]

| type    | data structure | data example                | notes                                                                                            |
| ------- | -------------- | --------------------------- | ------------------------------------------------------------------------------------------------ |
| code    | string         | `"X_SELL"`                  | it's the identifier of the association type                                                      |
| labels  | string[]       | `["en_US" => "Croll sell"]` | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface* |


### Attribute Group
        
        array:4 [
          "code" => "other"
          "sort_order" => 100
          "attributes" => array:19 [
            0 => "sku"
            1 => "a_date"
            2 => "a_file"
            3 => "an_image"
            4 => "a_metric"
            5 => "a_multi_select"
            6 => "a_number_float"
            7 => "a_number_float_negative"
            8 => "a_number_integer"
            9 => "a_price"
            10 => "a_ref_data_multi_select"
            11 => "a_ref_data_simple_select"
            12 => "a_simple_select"
            13 => "a_text"
            14 => "a_text_area"
            15 => "a_yes_no"
            16 => "a_localizable_image"
            17 => "a_scopable_price_with_decimal"
            18 => "a_localized_and_scopable_text_area"
          ]
          "labels" => array:2 [
            "en_US" => "Other"
            "fr_FR" => "Autre"
          ]
        ]

| type       | data structure | data example                               | notes                                                                                                                                                                   |
| ---------- | -------------- | ------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| code       | string         | `"other"`                                  | it's the identifier of the attribute group                                                                                                                              |
| sort_order | integer        | `0`                                        |                                                                                                                                                                         |
| attributes | string[]       | `[0 => "sku", 1 => "a_date"]`              | each element of the array represents the *code* of the *Pim\Component\Catalog\Model\AttributeInterface*. Order is defined by property *sortOrder* in AttributeInterface |
| labels     | string[]       | `["en_US" => "Other", "fr_FR" => "Autre"]` | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface*                                                                        |


### Category

        array:3 [
          "code" => "winter"
          "parent" => "master"
          "labels" => array:2 [
            "en_US" => "Winter",
            "fr_FR" => "Hiver"
          ]
        ]

| type    | data structure | data example                                | notes                                                                                            |
| ------- | -------------- | ------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| code    | string         | `"other"`                                   | it's the identifier of the category                                                              |
| parent  | string         | `null`                                      | it represents the *code* of the object *Akeneo\Component\Classification\Model\CategoryInterface* |
| labels  | array          | `["en_US" => "Winter", "fr_FR" => "Hiver"]` | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface* |


### Channel

        array:6 [
          "code" => "tablet"
          "labels" => [
            "en_US" => "Tablet"
            "fr_FR" => "Tablette"
          ]
          "currencies" => array:1 [
            0 => "USD"
          ]
          "locales" => array:1 [
            0 => "en_US"
          ]
          "category_tree" => "master"
          "conversion_units" => array:3 [
            "a_metric" => "KILOWATT"
            "a_metric_negative" => "CELSIUS"
            "a_metric_to_not_convert" => null
          ]
        ]

| type             | data structure | data example                                   | notes                                                                                                                                                                                                                                                  |
| ---------------- | -------------- | ---------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| code             | string         | `"ecommerce"`                                  | it's the identifier of the channel                                                                                                                                                                                                                     |
| labels           | string[]       | `["en_US" => "Tablet", "fr_FR" => "Tablette"]` | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface*                                                                                                                                                       |
| currencies       | string[]       | `[0 => "USD", "1 => "EUR"]`                    | each element of the array represents the *code* of the *Pim\Component\Catalog\Model\CurrencyInterface*                                                                                                                                                 |
| locales          | string[]       | `[0 => "en_US", 1 => "fr_FR"]`                 | each element of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface*                                                                                                                                                   |
| category_tree    | string         | `"master"`                                     | only root category. It represents the *code* of the object *Akeneo\Component\Classification\Model\CategoryInterface*                                                                                                                                   |
| conversion_units | string[]       |                                                | keys of each element of the array represent the *code* of the *Pim\Component\Catalog\Model\AttributeInterface*. Values of each element of the array represent one of the constant in classes of *Akeneo/Bundle/MeasureBundle/Family/*, except *FAMILY* |


### Currency

        array:2 [
          "code" => "USD"
          "enabled" => true
        ]

| type    | data structure | data example | notes                               |
| ------- | -------------- | ------------ | ----------------------------------- |
| code    | string         | `"USD"`      | it's the identifier of the currency |
| enabled | boolean        | `false`      |                                     |


### Family

        array:5 [
          "code" => "my_family"
          "labels" => array:1 [
            "en_US" => "My family"
          ]
          "attributes" => array:4 [
            0 => "a_number_float"
            1 => "a_price"
            2 => "sku"
            3 => "image"
          ]
          "attribute_as_label" => "sku"
          "attribute_as_image" => "image"
          "attribute_requirements" => array:1 [
            "ecommerce" => array:2 [
              0 => "a_price"
              1 => "sku"
            ]
          ]
          "family_variants" => array:2 [
            0 => "a_family_variant",
            1 => "another_family_variant"
          ]
        ]
        
| type                   | data structure | data example                                                             | notes                                                                                                              |
| ---------------------- | -------------- | ------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------ |
| code                   | string         | `"my_family"`                                                            | it's the identifier of the family                                                                                  |
| labels                 | string[]       | `["en_US" => "My family"]`                                               | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface*                   |
| attributes             | string[]       | `[0 => "sku"]`                                                           | each element of the array represents the *code* of the *Pim\Component\Catalog\Model\AttributeInterface*            |
| attribute_as_label     | string         | `"sku"`                                                                  | it represents the *code* of the object *Pim\Component\Catalog\Model\AttributeInterface* used as label              |
| attribute_as_image     | string         | `"image"`                                                                | it represents the *code* of the object *Pim\Component\Catalog\Model\AttributeInterface* used as image. Can be null |
| attribute_requirements | array          | `["ecommerce" => [0 => "sku", "a_text_area"], "tablet" => [0 => "sku"]]` | each element of the array represents the *code* of the *Pim\Component\Catalog\Model\AttributeInterface*            |
| family_variants        | array          | `[0 => "a_family_variant", 1 => "another_family_variant"]`               | each element of the array represents the *code* of the *Pim\Component\Catalog\Model\FamilyVariantInterface*            |


### Family variant

        array:4 [
          "code" => "my_family_variant"
          "family" => "family"
          "labels" => array:2 [
            "en_US" => "My family variant"
            "fr_FR" => "Ma variation de famille"
          ]
          "variant_attribute_sets" => array:1 [
            0 => array:3 [
              "level" => 1
              "axes" => array:1 [
                0 => "a_simple_select"
              ]
              "attributes" => array:2 [
                0 => "an_attribute"
                1 => "an_other_attribute"
              ]
            ]
          ]
        ]

| type                   | data structure | data example                                                                                               | notes                                                                                                   |
| ---------------------- | -------------- | ---------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| code                   | string         | `"my_family_variant"`                                                                                      | it's the identifier of the family variant                                                                |
| family                 | string         | `"family"`                                                                                                 | the code of the family of the family variant                                                            |
| labels                 | array          | `["en_US" => "My family variant", "fr_FR" => "Ma variation de famille"]`                                   | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface*        |
| variant_attribute_sets | array          | `[["level" => 1, "axes" => ["a_simple_select_attribute"], "attributes" => ["a_simple_select_attribute", "an_attribute", "an_other_attribute"]]]`, | an array containing the 3 following keys: `level` which is an integer always stricly higher than 0, `axes` and `attributes` which an arrays where each element represents the *code* of the *Pim\Component\Catalog\Model\AttributeInterface* |

Regarding the array `variant_attribute_sets`, an attribute present in the `axes` field will also be present in the `attributes` field.


### Group
        
        array:3 [
          "code" => "my_group"
          "type" => "RELATED"
          "labels" => array:1 [
            "en_US" => "My group"
          ]
        ]

| type   | data structure | data example              | notes                                                                                            |
| ------ | -------------- | ------------------------- | ------------------------------------------------------------------------------------------------ |
| code   | string         | `"my_group"`              | it's the identifier of the group                                                                 |
| type   | string         | `"RELATED"`               |                                                                                                  |
| labels | array          | `["en_US" => "My group"]` | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface* |


### Group Type
        
        array:3 [
          "code" => "my_group_type"
          "labels" => array:1 [
            "en_US" => "My beautiful group type"
          ]
        ]
        
| type       | data structure | data example                  | notes                                                                                            |
| ---------- | -------------- | ----------------------------- | ------------------------------------------------------------------------------------------------ |
| code       | string         | `"my_group_type"`             | it's the identifier of the group type                                                            |
| labels     | array          | `["en_US" => "My beautiful group type"]` | each key of the array represents the *code* of the *Pim\Component\Catalog\Model\LocaleInterface* |

    
### Locale

        array:2 [
          "code" => "en_US"
          "enabled" => true
        ]
        
| type    | data structure  | data example | notes                             |
| ------- | --------------- | ------------ | --------------------------------- |
| code    | string          | `"fr_FR"`    | it's the identifier of the locale |
| enabled | boolean         | `false`      |                                   |


### File info

        array:5 [
          "code" => "d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.jpg"
          "original_filename" => "akeneo.jpg"
          "mime_type" => "image/jpeg"
          "size" => 10584,
          "extension" => "jpg"
        ]
  
| type              | data structure | data example                                                    | notes                                                                              |
| ----------------- | -------------- | --------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| code              | string         | `"d/d/1/2/dd12d8e12d6de43fe7c424d6ed0b45cc0aa6e20f_akeneo.jpg"` | it's the code of the file, generated by the application from the original filename |
| original_filename | string         | `"akeneo.jpg"`                                                  |                                                                                    |
| mime_type         | string         | `image/jpeg`                                                    | This data is determined by the application                                         |
| size              | integer        | `10584`                                                         | This data is determined by the application                                         |
| extension         | string         | `jpg`                                                           | This data is determined by the application                                         |


### Asset (Enterprise Edition)

        array:6 => [
          'code'        => "my_asset"
          'localized'   => false
          'description' => "description"
          'end_of_use'  => "2016-09-01T00:00:00+0200"
          'tags'        => array:1 [
            0 => "akeneo"
          ]
          'categories'  => array:1 [
            0 => "startup"
          ]
        ]
                                                      
| type        | data structure | data example                 | notes                                                                                                            |
| ----------- | -------------- | ---------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| code        | string         | `"my_asset"`                 | it's the identifier of the asset.                                                                                |
| localized   | boolean        | `true`                       |                                                                                                                  |
| description | string         | `"desc"`                     |                                                                                                                  |
| end_of_use  | string         | `"2016-09-01T00:00:00+0200"` | formatted to ISO-8601 (see above)                                                                                |
| tags        | string[]       | `[]`                         | each element of the array represents the *code* of the *PimEnterprise\Component\ProductAsset\Model\TagInterface* |
| categories  | string[]       | `[]`                         | each element of the array represents the *code* of the *Akeneo\Component\Classification\Model\CategoryInterface* |


### Asset Variation (Enterprise Edition)

        array:5 => [
          "code" => "f/4/d/1/f4d12ffbdbe628ba8e0b932c27f425130cc23535_imageA_variationA.jpg"
          "asset" => "my_asset"
          "locale" => "en_US"
          "channel" => "ecommerce"
          "reference_file" => "f/4/d/1/f4d12ffbdbe628ba8e0b932c27f425130cc23535_imageA.jpg"
        ]

| type           | data structure | data example                                                               | notes                                                                                              |
| -------------- | -------------- | -------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| code           | string         | `"f/4/d/1/f4d12ffbdbe628ba8e0b932c27f425130cc23535_imageA_variationA.jpg"` | it represents the *key* of the object *Akeneo\Component\FileStorage\Model\FileInfoInterface*       |
| asset          | string         | `"my_asset"`                                                               | it represents the *code* of the object *PimEnterprise\Component\ProductAsset\Model\AssetInterface* |
| locale         | string         | `"fr_FR"`                                                                  | it represents the *code* of the object *Pim\Component\Catalog\Model\LocaleInterface*               |
| channel        | string         | `"tablet"`                                                                 | it represents the *code* of the object *Pim\Component\Catalog\Model\ChannelInterface*              |
| reference_file | string      	  | `"f/4/d/1/f4d12ffbdbe628ba8e0b932c27f425130cc23535_imageA.jpg"`            | it represents the *key* of the object *Akeneo\Component\FileStorage\Model\FileInfoInterface*       |


### Channel configuration (Enterprise Edition)

        array:2 => [
          "channel" => "ecommerce"
          "configuration" => array:2 [
            "width" => 200
            "scale" => 2
          ]
        ]

| type           | data structure | data example                     | notes                                                                                 |
| -------------- | -------------- | -------------------------------- | ------------------------------------------------------------------------------------- |
| channel        | string         | `tablet`                         | it represents the *code* of the object *Pim\Component\Catalog\Model\ChannelInterface* |
| configuration  | array          | `["width" => 200, "scale" => 2]` |                                                                                       |


### Rule (Enterprise Edition)

        array:5 => [
          "code" => "my_rule"
          "type" => "product"
          "priority" => 0
          "conditions" => array:1 [
             0 => array:3 [
               "field" => "a_name"
               "operator" => "contains"
               "value" => "description"
             ]
          ]
          "actions" => array:1 [
            0 => array:3 [
              "type" => "set"
              "field" => "a_text_area"
              "value" => "the new description"
            ]
          ]
        ]

| type       | data structure | data example                                                                                                                                         | notes                                                                                                           |
| ---------- | -------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| code       | string         | `"my_rule"`                                                                                                                                          | it's the identifier of the rule                                                                                 |
| type       | string         | `"product"`                                                                                                                                          | it represents the *type* of the object *Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface*           |
| priority   | integer        | `100`                                                                                                                                                |                                                                                                                 |
| conditions | array          | `[0 => ["field" => "a_name", "operator" => "contains", "value" => "description"], 1 =>["field" => "a_text", "operator" => "!=", "value" => "name"]]` | each element of the array represents a condition for *Pim\Component\Catalog\Query\ProductQueryBuilderInterface* |
| actions    | array          | `[0 => ["type" => "set", "field" => "a_text_area", "value" => "the new description"]]`                                                               | each element of the array represents the action to apply when condition is fulfilled                            |


## Usage

The standard format is used to:

* import data
* export data
* update objects in memory (imports, PEF for products, Mass Edit)
* define the data expected in the `Pim\Component\Catalog\Query\ProductQueryBuilderInterface` filters
* store draft changes (EE)


## Next version?

### Add more information in product format

Currently, we have not enough information about the product values in the standard format. For instance, we don't know their attribute types. If we want this information, we have to request the database which can be quite consuming. We could add them, but we have to be careful as these data must not be updated during a POST for instance.

### Attribute format

Currently, all options of an attribute are returned in the standard format. For instance, keys `date_min` & `date_max` are returned for a number attribute even if it's not relevant. Specific options could be return in a key `parameters` for example.
