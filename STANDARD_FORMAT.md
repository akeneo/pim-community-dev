# Standard format

The standard format is a normalized array representation of the objects of the PIM. It it used to manipulate (query/update), describe or even sometimes store these objects *inside* the PIM. Currently it is *not* designed to provide a a representation of these objects outside the PIM.

The standard format is consistent in term of:

* structure: for instance, products will always be represented the same way
* data formatting: for instance, dates will always be formatted the same way

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


To avoid [loosing precision with floating points](http://floating-point-gui.de), and as [decimal type don't exist in PHP](http://php.net/manual/en/language.types.float.php), decimals are rendered as strings. If you need to perform precise operations on such numbers, please use [the arbitrary precision math functions](http://php.net/manual/en/ref.bc.php) or the [gmp functions](http://php.net/manual/en/ref.gmp.php).
For instance, the standard format of an object that contains the properties *a_decimal* and *a_negative_decimal* would be:
    
        array:2 [
          "a_decimal" => "46546.65987313"
          "a_negative_deciaml" => "-45.8981226"
        ]

Linked entities are represented only by their identifier as strings. For instance, the standard format of a *foo* object that has a link to an external *bar* object would be:
    
        array:1 [
          "bar" => "here is the identifier of the bar object"
        ]

## Product

### Common structure

The products contain inner fields and product values that are linked to attributes.
All products have the same fields (family, groups, variant groups, categories, associations, status, dates of creation and update) while product values are flexible among products.

Let's consider a *bar* product, without any product value, except its identifier *sku*. This product also contains:

* a family
* several groups
* a variant group
* several categories
* several associations related to groups and/or other products

Its standard format would be the following:
        
        array:9 [
          "family" => "familyA"
          "groups" => array:2 [
            0 => "groupA"
            1 => "groupB"
          ]
          "variant_group" => "variantA"
          "categories" => array:2 [
            0 => "categoryA1"
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
              "products" => array:2 [
                0 => "foo"
                1 => "baz"
              ]
            ]
            "UPSELL" => array:1 [
              "groups" => array:1 [
                0 => "groupA"
              ]
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

Family (key *family*), groups (key *groups*) variant group (key *variant_group*), categories (key *categories*) as well as products (key *associations.ASSOCIATION_NAME.products*) and groups (key *associations.ASSOCIATION_NAME.groups*) of the associations, are external objects to the product. So they are represented by their identifier as strings.

Date of creation (key *created*) and update (key *updated*) are datetimes.

Status (key *enabled*) is a boolean.

TODO: identifier in the root

### Product values

Let's now consider a catalog with all attribute types possible and a *foo* product, that contains:

* all the attributes of the catalog
* a family
* several groups
* a variant group
* several categories
* several associations related to groups and/or other products

Its standard format would be the following:

        array:9 [
          "family" => "familyA"
          "groups" => array:2 [
            0 => "groupA"
            1 => "groupB"
          ]
          "variant_group" => "variantA"
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
                "data" => "42.0000"
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
                "data" => ""this is a text""
              ]
            ]
            "a_text_area" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => ""this is a very very very very very long  text""
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
                "data" => "a text area for eccommerce in English"
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
                  "data" => "987654321987.123456789123"
                  "unit" => "KILOWATT"
                ]
              ]
            ]
            "a_price" => array:1 [
              0 => array:3 [
                "locale" => null
                "scope" => null
                "data" => array:2 [
                  0 => array:2 [
                    "data" => "45.00"
                    "currency" => "USD"
                  ]
                  1 => array:2 [
                    "data" => "56.53"
                    "currency" => "EUR"
                  ]
                ]
              ]
            ]
            "a_scopable_price" => array:2 [
              0 => array:3 [
                "locale" => null
                "scope" => "ecommerce"
                "data" => array:2 [
                  0 => array:2 [
                    "data" => "15.00"
                    "currency" => "EUR"
                  ]
                  1 => array:2 [
                    "data" => "20.00"
                    "currency" => "USD"
                  ]
                ]
              ]
              1 => array:3 [
                "locale" => null
                "scope" => "tablet"
                "data" => array:2 [to avoid loosing precision with floating point 
                  0 => array:2 [
                    "data" => "17.00"
                    "currency" => "EUR"
                  ]
                  1 => array:2 [
                    "data" => "24.00"
                    "currency" => "USD"
                  ]
                ]
              ]
            ]
          ]
          "created" => "2016-06-23T11:24:44+02:00"
          "updated" => "2016-06-23T11:24:44+02:00"
          "associations" => array:3 [to avoid loosing precision with floating point 
            "PACK" => array:1 [
              "products" => array:2 [
                0 => "bar"
                1 => "baz"
              ]
            ]
            "UPSELL" => array:1 [
              "groups" => array:1 [
                0 => "groupA"
              ]
            ]
            "X_SELL" => array:2 [
              "groups" => array:1 [
                0 => "groupB"
              ]
              "products" => array:1 [
                0 => "bar"
              ]to avoid loosing precision with floating point 
            ]
          ]
        ]

The product values are provided via the key *values*.

Product values can be localisable and/or scopable:

* *localisable* means its value depends of the locale
* *scopable* means its value depends of the scope (also called channel)
* *localisble and scopable* means its value depends of the locale and the scope (also called channel)

That's why product values always respect the following structure:

        array:3 [
            "locale" => "a locale code"
            "scope" => "a scope code"
            "data" => "the value for the given locale and scope"
        ]

And that's why, for the same attribute, you can have multiple product values:

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

All types of attributes (except the *identifier*) can be localisable and/or scopable. In the example above:
 
* there is a localisable image: *a_localizable_image*
* there is a scopable price: *a_scopable_price*
* there is a scopable and localisable text area: *a_localized_and_scopable_text_area*

Depending on the type of the product value, the *data* key can have different structure:

| attribute type               	| data structure 	| data example                                                                                         	| notes                                                                                                             	|
|------------------------------	|----------------	|------------------------------------------------------------------------------------------------------	|-------------------------------------------------------------------------------------------------------------------	|
| identifier                   	| string         	| "foo"                                                                                                	|                                                                                                                   	|
| file                         	| string         	| "f/2/e/6/f2e6674e076ad6fafa12012e8fd026acdc70f814_fileA.txt"                                         	| it represents the *key* of the object *Akeneo\Component\FileStorage\Model\FileInfoInterface*                      	|
| image                        	| string         	| "f/4/d/1/f4d12ffbdbe628ba8e0b932c27f425130cc23535_imageA.jpg"                                        	| it represents the *key* of the object *Akeneo\Component\FileStorage\Model\FileInfoInterface*                      	|
| date                         	| string         	| "2016-06-13T00:00:00+02:00"                                                                          	| formatted to ISO-8601 (see above)                                                                                 	|
| multi select                 	| array          	| [0 => "optionA", 1 => "optionB"]                                                                     	| each element of the array represents the *code* of the *Pim\Component\Catalog\Model\AttributeOptionInterface*     	|
| number                       	| string         	| "-99.8732"                                                                                           	| formatted as a string to avoid the floating point precision problem of PHP (see above)                            	|
| reference data multi select  	| array          	| [0 => "fabricA",1 => "fabricB"]                                                                      	| each element of the array represents the *code* of the *Pim\Component\ReferenceData\Model\ReferenceDataInterface* 	|
| simple select                	| string         	| "optionB"                                                                                            	| it represents the *code* of the *Pim\Component\Catalog\Model\AttributeOptionInterface*                            	|
| reference data simple select 	| string         	| "colorB"                                                                                             	| it represents the *code* of the *Pim\Component\ReferenceData\Model\ReferenceDataInterface*                        	|
| text                         	| string         	| "this is a text"                                                                                     	|                                                                                                                   	|
| text area                    	| string         	| "this is a very very very very very long,text"                                                       	|                                                                                                                   	|
| yes/no                       	| boolean        	| true                                                                                                 	|                                                                                                                   	|
| metric                       	| array          	| ["data" => "987654321987.123456789123","unit" => "KILOWATT"]                                         	| *data* and *unit* keys are expected *unit* should be a know unit depending of the metric family of the attribute  	|
| price collection             	| array          	| [    0 => ["data" => "45.00","currency" => "USD"],    1 => ["data" => "56.53","currency" => "EUR"] ] 	| *data* and *currency* keys are exepected for each price *currency* should be a known currency                     	|


The following product values data, that represents decimal values, are represented with strings in the standard format:

* metric (class *Pim\Component\Catalog\Model\MetricIn noms de méthodes et de variables.terface*)
* price (class *Pim\Component\Catalog\Model\ProductPriceInterface*)
* number (class *Pim\Component\Catalog\Model\ProductValueInterface*, property *getDecimal*)


## Other entities

TODO: labels

## Usage

TODO

The standard format is used:

* imports
* exports
* update objects in memory (imports + PEF for products)
* data for PQB filters
* store variant groups values
* store draft changes (EE)

The objective is in the future is to use it:

* as versionning format in order to store the history of all entities in the database
* MAYBE TODO be a base layer for an external REST API
