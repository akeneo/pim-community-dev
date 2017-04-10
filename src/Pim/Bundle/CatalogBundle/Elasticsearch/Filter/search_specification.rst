Search specifications ElasticSearch on Akeneo
=============================================

Needs
-----
 - apply filter on all different attribute types
 - apply sorter on all sortable attribute types (excluding for example image and multi-select)

Implementation
--------------
Query vs filters
****************
Elasticsearch proposes two ways to search documents: filters and queries. According to the official documentation ( http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/_queries_and_filters.html ), the two differences between them are:

 - relevancy: query computes relevancy (scoring) and filter no
 - full text search: filter is about exact value whereas query can do full text search (analyzed)

According to the documentation:

   "As a general rule, use query clauses for full-text search or for any condition that should affect
   the relevance score, and use filter clauses for everything else."

So in our case of the grid, where relevancy is not applied, Akeneo PIM's filters will be mostly implemented with
Elasticsearch filters, except for CONTAINS type filters that needs full-text search.


Analyzers and dynamic mapping
*****************************
Depending on the field format (string, number, date, etc....), some specific analyzers maybe needed. For example, in case of ``identifier``, a n-gram analyzer must be added to be able to search on substring. Another example are the strings that needs a multifield to store the tokenized version for full-text search purpose and an untokenized version for sorting purpose.

As new attributes can be added dynamically to Akeneo, we will use the dynamic mapping feature of Elasticsearch and provides specific suffix that will specify the analyzer to use.
The suffixes are simply equivalent to the attribute's backend type.

For example:
 - description-text: the ``-text`` suffix is applied, meaning that we must apply a specific analyzer for a text area attribute.

List of fields and their mapping to Akeneo
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


List of attributes and their mapping to Akeneo
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

=================================   ==========================
Akeneo attribute type               Elasticsearch field suffix
=================================   ==========================
 pim_catalog_identifier              -varchar
 pim_catalog_text                    -varchar
 pim_catalog_textarea                -text
 pim_catalog_metric                  -metric
 pim_catalog_boolean                 -bool
 pim_catalog_simpleselect            -option
 pim_catalog_number                  -decimal
 pim_catalog_multiselect             -options
 pim_catalog_date                    -date
 pim_catalog_price_collection        -prices
 pim_catalog_image                   -media
 pim_catalog_file                    -media
 pim_reference_data_simpleselect     -reference_data_option
 pim_reference_data_multiselect      -reference_data_options
=================================   ==========================

Common elements
***************
Naming
~~~~~~
 - Elasticsearch fields for attribute follow this naming scheme:

``attribute_code-es_suffix.channel.locale``

- When the attribute is not scopable: ``channel`` becomes ``<all_channels>``
- When the attribute is not localizable: ``locale`` becomes ``<all_locales>``

Fitering
~~~~~~~~
The following example are the content of the ``query`` node in Yaml representation of the Elasticsearch JSON DSL.

They can take two different forms in our case:

If there's no Akeneo filter needing full-text capability, we will perform a ``filtered``
search without query and with only a ``bool`` filter with ``must`` typed occurence of the following form (as
Akeneo product builder supported only AND relation between conditions):

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'term' => [
                        'name-text.<all_channels>.<all_locales>' => 'Tshirt',
                    ],
                    'prefix' => [
                        'name-text.<all_channels>.<all_locales>' => 'Tshirt'
                    ]
                ]
            ]
        ]
    ]

If we have one or more filter needing full-text capability, we will need to combine query
and filter with a ``bool`` query with ``must`` typed occurence of the following form:

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'match_phrase' => [
                        'description-text.mobile.en_US' => '30 pages'
                    ],
                    'match_phrase' => [
                            'name-text.<all_channels>.<all_locales>' => "canon"
                    ],
                    'prefix' => [
                        'name-text.<all_channels>.<all_locales>' => 'Tshirt'
                    ],
                    'term' => [
                        'price-prices.<all_channels>.<all_locales>.USD' => 30
                    ]
                ]
            ]
        ]
    ]

Sorting
~~~~~~~
 - sorting will be applied with the following ``sort`` node:

.. code-block:: php

    'sort' => [
        'name-varchar.<all_channels>.<all_locales>' => "asc"
    ]

Sorting and tokenization
........................
Tokenized fields cannot be used for sorting as they will generate wrong results (see http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/multi-fields.html).

For those fields (mainly string fields), a multi-fields must be created with the untokenized value stored in a ``raw`` subfield.

In this case, the sort becomes:

.. code-block:: php

    'sort' => [
        'name-text.<all_channels>.<all_locales>.raw' => 'asc'
    ]

Text area
*********

:Apply: pim_catalog_textarea attributes
:Analyzer: HTML char filter + standard tokenizer + lowercase token filter

    Other fields analyzer:
     - raw: Keyword datatype + non-tokenized (Keyword Tokenizer) + lower case token filter

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'my_description-text' => [
                'mobile' => [
                    'fr_FR' => 'My description'
                ]
            ]
        ]
    ]



Filtering
~~~~~~~~~
Operators
.........
STARTS WITH
"""""""""""
:Specific field: raw

    Must be applied on the non-analyzed version of the field or will try to
    match on all tokens.

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'values.description-text.<all_channels>.<all_locales>.raw',
            'query' => "My*"
        ]
    ]

Note: All spaces must be escaped (with ``\\``) to prevent interpretation as separator. This applies on all query using a query_string.


Example:

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'values.description-text.<all_channels>.<all_locales>.raw',
            'query' => 'My\\ description*'
        ]
    ]


CONTAINS
""""""""
:Specific field: raw

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'values.description-text.<all_channels>.<all_locales>.raw',
            'query' => '*cool\\ product*'
        ]
    ]

DOES NOT CONTAIN
""""""""""""""""
:Specific field: raw

Same syntax than the ``contains`` but must be included in a ``must_not`` boolean occured type instead of ``filter``.

.. code-block:: php

    'bool' => [
        'must_not' => [
            'query_string' => [
                'default_field' => 'values.description-text.<all_channels>.<all_locales>.raw',
                'query' => '*cool\\ product*'
            ]
        ],
        'filter' => [
            'exists' => ['field' => 'values.description-text.<all_channels>.<all_locales>.raw'
        ]
    ]

Equals (=)
""""""""""
:Type: Filter
:Specific field: raw

    Equality will not work with tokenized field, so we will use the untokenized sub-field:

.. code-block:: php

    'filter' => [
        'term' => [
            'values.description-text.<all_channels>.<all_locales>.raw' => 'My full lookup text'
        ]
    ]

Not Equals (!=)
"""""""""""""""
:Type: Filter
:Specific field: raw

        Equality will not work with tokenized field, so we will use the untokenized sub-field:

.. code-block:: php

    'must_not' => [
        'term' => [
            'values.description-text.<all_channels>.<all_locales>.raw' => 'My full lookup text'
        ]
    ],
    'filter' => [
        'exists' => [
            'field' => 'values.description-text.<all_channels>.<all_locales>.raw'
        ]
    ]

EMPTY
"""""

.. code-block:: php

    'must_not' => [
        'exists => [
            'field' => 'values.description-text.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""

.. code-block:: php

    'filter' => [
        'exists => [
            'field' => 'values.description-text.<all_channels>.<all_locales>'
        ]
    ]

Enabled
*******
:Apply: apply datatype 'boolean' on the 'enabled' field

Data model
~~~~~~~~~~
.. code-block:: yaml

    enabled: true

Filtering
~~~~~~~~~
Operators
.........
Equals (=)
~~~~~~~~~~

.. code-block:: php

    'filter' => [
        'term' => [
            'enabled' => true
        ]
    ]

Not Equal (!=)
~~~~~~~~~~~~~~

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'must_not' => [
                    'term' => [
                        'enabled' => false
                    ]
                ],
                'filter' => [
                    'exists' => [
                        'field' => 'enabled'
                    ]
                ]
            ]
        ]
    ]

Text
****

:Apply: pim_catalog_text attributes
:Analyzer: keyword tokenizer + lowercase token filter

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'name-varchar' => [
                'mobile' => [
                    'fr_FR' => 'My product name'
                ]
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........
All operators except CONTAINS and DOES NOT CONTAINS are the same than with the text_area attributes but apply on the field directly instead of the ``.raw`` subfield.

CONTAINS
""""""""
.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'name-varchar',
            'query' => '*my_text*'
        ]
    ]

Note:
In case of performances problems, a faster solution would be to add a subfield with a n-gram analyzer.

DOES NOT CONTAIN
""""""""""""""""

Same syntax than the contains but must be include in a ``must_not`` boolean occured type instead of ``filter``.

.. code-block:: yaml

    'query' => [
        'bool' => [
            'must_not' => [
                'query_string' => [
                    'default_field' => 'name-varchar',
                    'query' => '*my_text*'
                ]
            ],
            'filter' => [
                'exists' => ['field' => 'name-varchar']
            ]
        ]
    ]

Identifier
**********
:Apply: apply datatype 'keyword' on the 'identifier' field
:Normalizer: Lowercase normalizer

Data model
~~~~~~~~~~
.. code-block:: yaml

  identifier: "PRCT-1256"

Filtering
~~~~~~~~~
Operators
.........
All operators are the same as the Text field type except for the 'EMPTY' and 'NOT EMPTY' operators.

STARTS WITH
"""""""""""

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'identifier',
            'query' => "sku-*"
        ]
    ]

CONTAINS
""""""""

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'identifier',
            'query' => '*00*'
        ]
    ]

DOES NOT CONTAIN
""""""""""""""""
Same syntax than the ``contains`` but must be included in a ``must_not`` boolean occured type instead of ``filter``.

.. code-block:: php

    'bool' => [
        'must_not' => [
            'query_string' => [
                'default_field' => 'identifier',
                'query' => '*00*'
            ]
        ],
        'filter' => [
            'exists' => ['field' => 'identifier']
        ]
    ]

Equals (=)
""""""""""

.. code-block:: php

    'filter' => [
        'term' => [
            'identifier' => 'sku-0011'
        ]
    ]

Not Equal (!=)
""""""""""""""

.. code-block:: php

    'bool' => [
        'must_not' => [
            'term' => [
                'identifier' => 'sku-0011'
            ]
        ],
        'filter' => [
            'exists' => [
                'field' => 'identifier'
            ]
        ]
    ]

In list
"""""""

.. code-block:: php

    'filter' => [
        'terms' => [
            'identifier' => ['sku-001', 'sku-0011']
        ]
    ]

Not In list
"""""""""""

.. code-block:: php

    'must_not' => [
        'terms' => [
            'identifier' => ['sku-001', 'sku-0011']
        ]
    ]

Media
*****

:Apply:
    pim_catalog_image and pim_catalog_file attributes

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'an_image-media' => [
                'mobile' => [
                    'fr_FR' => [
                        'extension'         => 'jpg',
                        'hash'              => 'the_hash',
                        'key'               => 'the/relative/path/to_akeneo.png',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => 42,
                        'storage'           => 'catalogStorage',
                    ]
                ]
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........
STARTS WITH
"""""""""""
:Type: filter
:Specific field: original_filename

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'values.an_image-media.<all_channels>.<all_locales>.original_filename',
            'query' => "ak*"
        ]
    ]

CONTAINS
""""""""
:Type: filter
:Specific field: original_filename

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'values.an_image-media.<all_channels>.<all_locales>.original_filename',
            'query' => '*neo*'
        ]
    ]

DOES NOT CONTAIN
""""""""""""""""
:Type: must_not
:Specific field: original_filename

Same syntax than the ``contains`` but must be included in a ``must_not`` boolean occured type instead of ``filter``.

.. code-block:: php

    'bool' => [
        'must_not' => [
            'query_string' => [
                'default_field' => 'values.an_image-media.<all_channels>.<all_locales>.original_filename',
                'query' => '*ziggy*'
            ]
        ],
        'filter' => [
            'exists' => ['field' => 'values.an_image-media.<all_channels>.<all_locales>'
        ]
    ]

Equals (=)
""""""""""
:Type: filter
:Specific field: original_filename

.. code-block:: php

    'filter' => [
        'term' => [
            'values.an_image-media.<all_channels>.<all_locales>.original_filename' => 'akeneo.jpg'
        ]
    ]

Not Equals (!=)
"""""""""""""""
:Type: must_not
:Specific field: original_filename

.. code-block:: php

    'must_not' => [
        'term' => [
            'values.an_image-media.<all_channels>.<all_locales>.original_filename' => 'ziggy.png'
        ]
    ],
    'filter' => [
        'exists' => [
            'field' => 'values.an_image-media.<all_channels>.<all_locales>'
        ]
    ]

EMPTY
"""""
:Type: filter

.. code-block:: php

    'must_not' => [
        'exists => [
            'field' => 'values.an_image-media.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'exists => [
            'field' => 'values.an_image-media.<all_channels>.<all_locales>'
        ]
    ]

Date
****
:Apply:
  pim_catalog_date attributes

Data model
~~~~~~~~~~

.. code-block:: yaml

  'values' => [
      'publishedOn-date' => [
          '<all_channels>' => [
              '<all_locales>' => '2015-02-24'
          ]
      ]
  ]

Filtering
~~~~~~~~~
Operators
.........
Less than (<)
"""""""""""""
:Type: filter

.. code-block:: php

    'range' => [
        'values.publishedOn-date.<all_channels>.<all_locales>' => [
            'lt' => '2015-02-26'
        ]
    ]


Equals (=)
""""""""""
:Type: filter

.. code-block:: php

    'term' => [
        'values.publishedOn-date.<all_channels>.<all_locales>' => '2015-02-26'
    ]

NOT EQUAL (!=)
""""""""""""""
:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'must_not' => [
                    'term' => [
                        'values.publishedOn-date.<all_channels>.<all_locales>' => '2015-02-26'
                    ]
                ],
                'filter' => [
                    'exists' => [
                        'field' => 'values.publishedOn-date.<all_channels>.<all_locales>'
                    ]
                ]
            ]
        ]
    ]

BETWEEN
"""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'range' => [
            'values.publishedOn-date.<all_channels>.<all_locales>' => [
                'gte' => '2017-03-22',
                'lte' => '2017-03-23'
            ],
        ]
    ]


NOT BETWEEN
"""""""""""
:Type: filter

.. code-block:: php

    'query' => [
        'bool' => [
            'must_not' => [
                'range' => [
                    'values.publishedOn-date.<all_channels>.<all_locales>' => [
                        'gte' => '2017-03-22',
                        'lte' => '2017-03-23'
                    ],
                ]
            ],
            'filter' => ['exists' => 'values.publishedOn-date.<all_channels>.<all_locales>']
        ]
    ]

Greater than (>)
""""""""""""""""
:Type: filter

.. code-block:: php

    'range' => [
        'values.publishedOn-date.<all_channels>.<all_locales>' => [
            'gt' => '2015-02-26'
        ]
    ]

EMPTY
"""""
:Type: filter

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'values.publishedOn-date.<all_channels>.<all_locales>',
        ]
    ]

Decimal
*******
:Apply:
 pim_catalog_number attributes

Please note that number attributes must be indexed as a string to be captured by the dynamic mapping. This way, the PIM doesn't need to manage float or integer questions.


Data model
~~~~~~~~~~
.. code-block:: yaml

  values.packet_count-decimal.<all_channels>.<all_locales>: 5

Filtering
~~~~~~~~~
Operators
.........
Less than (<)
"""""""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'range' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => ['lt' => 10]
        ]
    ]

Less than or equals to (<=)
"""""""""""""""""""""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'range' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => ['lte' => 10]
        ]
    ]

Equals (=)
""""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'term' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => 5
        ]
    ]

Not Equal (!=)
""""""""""""""
:Type: filter and must_not

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'must_not' => [
                    'term' => [
                        'values.packet_count-decimal.<all_channels>.<all_locales>' => 5
                    ]
                ],
                'filter' => [
                    'exists' => [
                        'field' => 'values.packet_count-decimal.<all_channels>.<all_locales>'
                    ]
                ]
            ]
        ]
    ]


Greater than or equal to (>=)
"""""""""""""""""""""""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'range' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => ['gte' => 10]
        ]
    ]

Greater than (>)
""""""""""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'range' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => ['gt' => 10]
        ]
    ]

EMPTY
"""""
:Type: must_not

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'values.packet_count-decimal.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.packet_count-decimal.<all_channels>.<all_locales>'
        ]
    ]


Option
******
:Apply: pim_catalog_simpleselect attributes

Data model
~~~~~~~~~~
.. code-block:: php

    'values' => [
        'color-option' => [
            '<all_channels>' => [
                '<all_locales>' => 'red'
            ]
        ]
    ]


Filtering
~~~~~~~~~
Operators
.........
IN
""
:Type: filter

.. code-block:: php

    'filter' => [
        'terms' => [
            'values.color-option.<all_channels>.<all_locales>' => ['red']
        ]
    ]

EMPTY
"""""
:Type: must_not

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'values.color-option.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.color-option.<all_channels>.<all_locales>'
        ]
    ]

NOT IN
""""""
:Type: must_not

.. code-block:: php

    'query' => [
        'bool' => [
            'must_not' => [
                'terms' => [
                    'values.color-option.<all_channels>.<all_locales>' => ['red']
                ]
            ],
            'filter' => [
                'exists' => [
                    'field' => 'values.color-option.<all_channels>.<all_locales>'
                ]
            ]
        ]
    ]

Sorting
~~~~~~~

.. code-block:: php

    'sort' => [
        'values.color-option.<all_channels>.<all_locales>' => [
            'order'   => 'asc',
            'missing' => '_first'
        ]
    ]

Simple select reference data
****************************
:Apply: pim_reference_data_simpleselect attributes

Data model
~~~~~~~~~~
.. code-block:: php

    'values' => [
        'brand-reference_data_option' => [
            '<all_channels>' => [
                '<all_locales>' => 'acme'
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........
IN
""
:Type: filter

.. code-block:: php

    'filter' => [
        'terms' => [
            'values.brand-reference_data_option.<all_channels>.<all_locales>' => ['acme']
        ]
    ]

EMPTY
"""""
:Type: filter

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'values.brand-reference_data_option.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.brand-reference_data_option.<all_channels>.<all_locales>'
        ]
    ]

NOT IN
""""""
:Type: must_not

.. code-block:: php

    'query' => [
        'bool' => [
            'must_not' => [
                'terms' => [
                    'values.brand-reference_data_option.<all_channels>.<all_locales>' => ['acme']
                ]
            ],
            'filter' => [
                'exists' => [
                    'field' => 'values.brand-reference_data_option.<all_channels>.<all_locales>'
                ]
            ]
        ]
    ]

Sorting
~~~~~~~
Sorting will be done on the localized label:

.. code-block:: php

    'sort' => [
        'values.brand-reference_data_option.<all_channels>.<all_locales>' => [
            'order'   => 'asc',
            'missing' => '_last'
        ]
    ]

Options
*******
:Apply: apply on the 'pim_catalog_multiselect' attributes

Data model
~~~~~~~~~~
.. code-block:: php

  values => [
      'my-tags-options' => [
          'mobile' => [
              'fr_FR' => ['summer', 'winter']
          ]
      ]
  ]

Filtering
~~~~~~~~~
Operators
.........

IN
""
:Type: filter

.. code-block:: php

    'terms' => [
        'values.my-tags-options.mobile.fr_FR' => ['summer']
    ]

NOT IN
""""""
:Type: must_not

.. code-block:: php

    'query' => [
        'bool' => [
            'filter' => [
                'exists' => [
                    'field' => 'values.my-tags-options.mobile.fr_FR'
                ]
            ],
            'must_not' => [
                'terms' => [
                    'values.my-tags-options.mobile.fr_FR' => ['summer']
                ]
            ]
        ]
    ]

IS EMPTY
""""""""
:Type: must_not

.. code-block:: php

    'exists' => [
        'field' => 'values.my-tags-options.mobile.fr_FR'
    ]

IS NOT EMPTY
""""""""""""
:Type: filter

.. code-block:: php

    'exists' => [
        'field' => 'values.my-tags-options.mobile.fr_FR'
    ]

Sorting
~~~~~~~
Not supported on that attribute_type

Reference data multi select
***************************

:Apply: pim_catalog_reference_data_multiselect attributes

Data model
~~~~~~~~~~
.. code-block:: php

    'values' => [
        'compatibility-reference_data_options' => [
            '<all_channels>' => [
                '<all_locales>' => ['windows_os', 'linux']
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........

IN
""
:Type: filter

.. code-block:: php

    'filter' => [
        'terms' => [
            'values.compatibility-reference_data_options.<all_channels>.<all_locales>' => ['windows_os', 'mac_os']
        ]
    ]

EMPTY
"""""
:Type: filter

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.compatibility-reference_data_options.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.compatibility-reference_data_options.<all_channels>.<all_locales>'
        ]
    ]

NOT IN
""""""
:Type: must_not

.. code-block:: php

    'query' => [
        'bool' => [
            'must_not' => [
                'terms' => [
                    'values.compatibility-reference_data_options.<all_channels>.<all_locales>' => ['windows_os', 'linux']
                ]
            ],
            'filter' => [
                'exists' => [
                    'field' => 'values.compatibility-reference_data_options.<all_channels>.<all_locales>'
                ]
            ]
        ]
    ]


Sorting
~~~~~~~
Not supported on that attribute_type

Metric
******
:Apply: pim_catalog_metric attributes

In case of metric, only the data converted to the default metric unit of the family is indexed, however the unit and data properties are also saved in ES but not indexed.

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'weight-metric' => [
                '<all_channels>' => [
                    '<all_locales> => [
                        'base_data' => '10.5559',
                        'base_unit' => 'KILOGRAM',
                        'data' => '10555.9',
                        'unit'  => 'GRAM'
                    ]
                ]
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........
All operators are identical to the one used on numbers.

Boolean
*******
:Apply: pim_catalog_boolean attributes

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'a_yes_no-boolean' => [
                'mobile' => [
                    'fr_FR' => true
                ]
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........
Equals (=)
""""""""""
:Type: filter

.. code-block:: php

    'filter' => [
        'term' => [
            'values.a_yes_no-boolean.<all_channels>.<all_locales>' => true
        ]
    ]

Not Equals (!=)
"""""""""""""""
:Type: must_not

.. code-block:: php

    'must_not' => [
        'term' => [
            'values.a_yes_no-boolean.<all_channels>.<all_locales>' => true
        ]
    ],
    'filter' => [
        'exists' => [
            'field' => 'values.a_yes_no-boolean.<all_channels>.<all_locales>'
        ]
    ]

Sorting
~~~~~~~
Operators
.........
ASCENDANT
"""""""""

.. code-block:: php

    'sort' => [
        'values.a_yes_no-boolean.ecommerce.en_US' => [
            'order'   => 'asc',
            'missing' => '_last'
        ]
    ]


DESCENDANT
""""""""""

.. code-block:: php

    'sort' => [
        'values.a_yes_no-boolean.ecommerce.en_US' => [
            'order'   => 'desc',
            'missing' => '_last'
        ]
    ]

Completeness
************
:Apply: 'completeness' field

Data model
~~~~~~~~~~
As completenesses are indexed by channel and locale, the "completeness" dynamic template is applied to this field. Completenesses' ratios are indexed as integers.

.. code-block:: yaml

    completeness:
        print:
            en_US: 100
            fr_FR: 89
        ecommerce:
            en_US: 85

Filtering
~~~~~~~~~
Operators
.........
All operators and syntax that apply on number apply as well on completeness, but by providing
the full path to the targeted completeness.

Example with the ``>`` operator:

.. code-block:: yaml

    range:
        completeness.print.en_US:
            gt: 4

Sorting
~~~~~~~

.. code-block:: php

    'sort' => [
        'completeness.mobile.en_US' => [
            'order'   => 'asc',
            'missing' => '_last'
        ]
    ]

Category
********
:Apply: apply 'keyword' datatype on 'categories' field
:Analyser: none

Data model
~~~~~~~~~~
.. code-block:: yaml

  categories: ['master', 'categoryA1', 'categoryB']

Filtering
~~~~~~~~~
Operators
.........
IN
~~
:Type: filter

.. code-block:: php

    'terms' => [
        'categories' => ['categoryA1']
    ]

NOT IN
~~~~~~
:Type: filter

Same as ``IN``, but with ``must_not`` occured type instead of ``filter``

UNCLASSIFIED
~~~~~~~~~~~~
:Type: filter

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'categories'
        ]
    ]

IN OR UNCLASSIFIED
~~~~~~~~~~~~~~~~~~
:Type: filter

We use the ``should`` occured type to join both conditions on a ``bool`` filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'should' => [
                    'terms' => [
                        'field' => [
                            'categories' => ['categoryA1']
                        ]
                    ]
                    'bool' => [
                        'must_not' => [
                            'exists' => [
                                'field' => 'categories'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]

IN CHILDREN
~~~~~~~~~~~
:Type: filter

This operator is the same than ``IN``, but works by providing the full list of children ids from the product. We need to check performances on this one to see if there's no other way than using ``IN`` to achieve better performances if needed.

NOT IN CHILDREN
~~~~~~~~~~~~~~~
:Type: filter

Same as above but with a ``must_not`` occured type

Price
*****
:Apply: pim_catalog_price_collection

Please note that just like number attributes, prices value must be indexed as a string to be captured by the dynamic mapping.

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'a_price-prices' => [
                'ecommerce' => [
                    'fr_FR' => [
                        'USD' => '125.53'
                        'EUR' => '110'
                    ]
                ]
            ]
        ]
    ]

Filtering
~~~~~~~~~
Same operators than ``number`` apply, but by using the full path to the price with its currency.

Example for the ``>`` operator:

.. code-block:: php

    [
        'filter' => [
            'range' => [
                'price-prices.<all_channels>.<all_locales>.USD' => [ 'gt' => 100 ]
            ]
        ]
    ]

Product id
**********
:Apply: id field

Product system ids coming from DB (autoincrement in ORM or MongoDBRef in MongoDB) are used as
the Elasticsearch ``"_id"`` field

.. code-block:: yaml

  _id: "54f96c28c1ad880c308b4b90"

Filtering
~~~~~~~~~
Operators
.........
Equals (=)
~~~~~~~~~~
:Type: filter

.. code-block:: yaml

    ids:
        values: ["54f96c28c1ad880c308b4b66"]

IN
~~
:Type: filter

    ::
        ids:
            values: ["54f96c28c1ad880c308b4b66","54f96c28c1ad880c308b4b7b"]

NOT IN
~~~~~~
:Type: filter

Same as ``IN``, but with the ``must_not`` occured type

Family
******
:Apply: apply datatype 'keyword' on the 'family' field

Data model
~~~~~~~~~~
.. code-block:: yaml

  family: 'camcorders'

Filtering
~~~~~~~~~
Operators
.........
IN LIST
"""""""

.. code-block:: php

    'filter' => [
        'terms' => [
            'family' => ['camcorders', 'mug'],
        ]
    ]

NOT IN LIST
"""""""""""

.. code-block:: php

    'must_not' => [
        'terms' => [
            'family' => ['camcorders'],
        ]
    ]

IS EMPTY
""""""""

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'family',
        ]
    ]

IS NOT EMPTY
""""""""""""

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'family',
        ]
    ]

DateTime (updated and created)
******************************
:Apply: datetime fields (updated and created)

Data model
~~~~~~~~~~
.. code-block:: yaml

  updated: '2017-03-22T22:42:10+01:00'

Filtering
~~~~~~~~~
Operators
.........
EQUALS
""""""

.. code-block:: php

    'filter' => [
        'term' => [
            'updated' => '2017-03-22T22:42:10+01:00',
        ]
    ]

LOWER THAN
""""""""""

.. code-block:: php

    'filter' => [
        'range' => [
            'updated' => ['lt' => '2017-03-22T22:42:10+01:00'],
        ]
    ]

GREATER THAN
""""""""""""

.. code-block:: php

    'filter' => [
        'range' => [
            'updated' => ['gt' => '2017-03-22T22:42:10+01:00'],
        ]
    ]

BETWEEN
"""""""

.. code-block:: php

    'filter' => [
        'range' => [
            'updated' => [
                'gte' => '2017-03-22T22:42:10+01:00',
                'lte' => '2017-03-23T22:42:10+01:00'
            ],
        ]
    ]

NOT BETWEEN
"""""""""""

.. code-block:: php

    'query' => [
        'bool' => [
            'must_not' => [
                'range' => [
                    'updated' => [
                        'gte' => '2017-03-22T22:42:10+01:00',
                        'lte' => '2017-03-23T22:42:10+01:00'
                    ],
                ]
            ],
            'filter' => ['exists' => 'updated']
        ]
    ]

IS EMPTY
""""""""

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'updated',
        ]
    ]

IS NOT EMPTY
""""""""""""

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'updated',
        ]
    ]

NOT EQUAL
"""""""""

.. code-block:: php

    'query' => [
        'bool' => [
            'must_not' => [
                'term' => [
                    'updated' => '2017-03-22T22:42:10+01:00'
                ]
            ],
            'filter' => [
                'exists' => [
                    'field' => 'updated'
                ]
            ]
        ]
    ]

SINCE LAST JOB
""""""""""""""
:Apply: Apply the GREATER THAN Operator with the date of the last execution of the job

SINCE LAST N DAYS
"""""""""""""""""
:Apply: Apply the GREATER THAN Operator with the date corresponding to the Nth previous day

Data model
~~~~~~~~~~
.. code-block:: yaml

    family: 'familyA'

Sorting
~~~~~~~
Sorting is done on the localized label:

.. code-block:: yaml

    sort:
        family.label-en_US: "asc"

Groups
******
:Apply: apply 'keyword' datatype on 'groups' field

Data model
~~~~~~~~~~
.. code-block:: yaml

    groups: ['groupA', 'groupB', 'groupC']

Filtering
~~~~~~~~~
Operators
.........
IN
~~
:Type: filter

.. code-block:: php

    'terms' => [
        'groups' => ['groupA', 'groupB', 'groupC']
    ]

NOT IN
~~~~~~
:Type: must_not

.. code-block:: php

    'terms' => [
        'groups' => ['groupA', 'groupB', 'groupC']
    ]

IS EMPTY
~~~~~~~~
:Type: must_not

.. code-block:: php

    ['exists' => ['field' => 'groups']]

IS NOT EMPTY
~~~~~~~~~~~~
:Type: filter

.. code-block:: php

    ['exists' => ['field' => 'groups']]


Sorting
~~~~~~~
For the group grid, we need to sort product in order to put them at the beginning of the list
when they belong to this particular list:

::

  TODO see function score to put product belonging at first and sort by relevancy

Associations
************
Filtering
~~~~~~~~~
No filtering expected on associations (no filter on the grid).

Sorting
~~~~~~~

::

  TODO see function score to put product belonging to the associations at first and sort by relevancy

Testing
-------
All queries above are (or should be) defined as Behat scenarios in the `queries_test` directory relative to this documentation.
