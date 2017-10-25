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
 - description-textarea: the ``-textarea`` suffix is applied, meaning that we must apply a specific analyzer for a text area attribute.

List of fields and their mapping to Akeneo
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


List of attributes and their mapping to Akeneo
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

=================================   ==========================
Akeneo attribute type               Elasticsearch field suffix
=================================   ==========================
 pim_catalog_identifier              -text
 pim_catalog_text                    -text
 pim_catalog_textarea                -textarea
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
The following examples are the content of the ``query`` node of the Elasticsearch JSON DSL.

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
                        'description-textarea.mobile.en_US' => '30 pages'
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

    [
        'sort' => [
            'name-text.<all_channels>.<all_locales>' => 'asc',
            'missing' => '_last'
        ]
    ]

Sorting and tokenization
........................
Tokenized fields cannot be used for sorting as they will generate wrong results (see http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/multi-fields.html).

For those fields (mainly string fields), a multi-fields must be created with the untokenized value stored in a dynamic field (for textarea, its name is ``preprocessed``).

In this case, the sort becomes:

.. code-block:: php

    'sort' => [
        'name-text.<all_channels>.<all_locales>.preprocessed' => 'asc',
        'missing' => '_last'
    ]

Text area
*********

:Apply: pim_catalog_textarea attributes
:Analyzer: Text datatype + HTML char filter + standard tokenizer + lowercase token filter

    Other fields analyzer:
     - preprocessed: Keyword datatype + non-tokenized (Keyword Tokenizer) + lower case token filter

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'my_description-textarea' => [
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
:Specific field: preprocessed

    Must be applied on the non-analyzed version of the field or will try to
    match on all tokens.

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
            'query' => "My*"
        ]
    ]

Note: All spaces must be escaped (with ``\\``) to prevent interpretation as separator. This applies on all query using a query_string.


Example:

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
            'query' => 'My\\ description*'
        ]
    ]


CONTAINS
""""""""
:Specific field: preprocessed

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
            'query' => '*cool\\ product*'
        ]
    ]

DOES NOT CONTAIN
""""""""""""""""
:Specific field: preprocessed

Same syntax than the ``contains`` but must be included in a ``must_not`` boolean occured type instead of ``filter``.

.. code-block:: php

    'bool' => [
        'must_not' => [
            'query_string' => [
                'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
                'query' => '*cool\\ product*'
            ]
        ],
        'filter' => [
            'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed'
        ]
    ]

Equals (=)
""""""""""
:Type: Filter
:Specific field: preprocessed

    Equality will not work with tokenized field, so we will use the untokenized sub-field:

.. code-block:: php

    'filter' => [
        'term' => [
            'values.description-textarea.<all_channels>.<all_locales>.preprocessed' => 'My full lookup textarea'
        ]
    ]

Not Equals (!=)
"""""""""""""""
:Type: Filter
:Specific field: preprocessed

        Equality will not work with tokenized field, so we will use the untokenized sub-field:

.. code-block:: php

    'must_not' => [
        'term' => [
            'values.description-textarea.<all_channels>.<all_locales>.preprocessed' => 'My full lookup textarea'
        ]
    ],
    'filter' => [
        'exists' => [
            'field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed'
        ]
    ]

EMPTY
"""""

.. code-block:: php

    'must_not' => [
        'exists => [
            'field' => 'values.description-textarea.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""

.. code-block:: php

    'filter' => [
        'exists => [
            'field' => 'values.description-textarea.<all_channels>.<all_locales>'
        ]
    ]

Sorting
~~~~~~~

The sorting operation is made on the preprocessed version of the textarea.

Operators
.........
ASCENDANT
"""""""""

.. code-block:: php

    'sort' => [
        'values.description-textarea.<all_channels>.<all_locales>.preprocessed' => [
            'order' => 'ASC',
            'missing' => '_last'
        ]
    ]


DESCENDANT
""""""""""

.. code-block:: php

    'sort' => [
        'values.description-textarea.<all_channels>.<all_locales>.preprocessed' => [
            'order' => 'DESC',
            'missing' => '_last'
        ]
    ]

Enabled
*******
:Apply: apply datatype 'boolean' on the 'enabled' field

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'enabled-boolean' => [
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

Sorting
~~~~~~~

The sorting operation is made on the preprocessed version of the textarea.

Operators
.........
ASCENDANT
"""""""""

.. code-block:: php

    'sort' => [
        'enabled-boolean' => [
            'order'   => 'ASC',
            'missing' => '_last'
        ]
    ]

DESCENDANT
""""""""""

.. code-block:: php

    'sort' => [
        'enabled-boolean' => [
            'order'   => 'DESC',
            'missing' => '_last'
        ]
    ]

Text
****

:Apply: pim_catalog_text attributes
:Analyzer: Keyword datatype + lowercase token filter

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'name-text' => [
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

All operators except CONTAINS and DOES NOT CONTAINS are the same than with the textarea attributes but apply on the field directly instead of the ``.preprocessed`` subfield.

CONTAINS
""""""""
.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'name-text',
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
                    'default_field' => 'name-text',
                    'query' => '*my_text*'
                ]
            ],
            'filter' => [
                'exists' => ['field' => 'name-text']
            ]
        ]
    ]

Sorting
~~~~~~~
Operators
.........
Ascendant
"""""""""

.. code-block:: php

    'sort' => [
        'values.name-text.<all_channels>.<all_locales>' => [
            'order'   => 'ASC',
            'missing' => '_last'
        ]
    ]

Descendant
""""""""""

.. code-block:: php

    'sort' => [
        'values.name-text.<all_channels>.<all_locales>' => [
            'order'   => 'DESC',
            'missing' => '_last'
        ]
    ]

Id
**
:Apply: apply datatype 'keyword' on the 'id' field

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'id' => '42'
    ]

Filtering
~~~~~~~~~
Operators
.........
All operators are the same as the Text field type except that the 'EMPTY' and 'NOT EMPTY' operators do not exists for this property.

Equals (=)
""""""""""

.. code-block:: php

    'filter' => [
        'term' => [
            'id' => '42'
        ]
    ]

Not Equal (!=)
""""""""""""""

.. code-block:: php

    'must_not' => [
        'term' => [
            'id' => '42'
        ]
    ],
    'filter' => [
        'exists' => [
            'field' => 'id'
        ]
    ]

In list
"""""""

.. code-block:: php

    'filter' => [
        'terms' => [
            'id' => ['21', '42']
        ]
    ]

Not In list
"""""""""""

.. code-block:: php

    'must_not' => [
        'terms' => [
            'id' => ['21', '42']
        ]
    ]

Sorting
~~~~~~~
Operators
.........

Whenever one wants to sort on the field 'id'. The sort query will be performed on the field 'id'.

ASCENDANT
"""""""""

.. code-block:: php

    'sort' => [
        'id' => 'ASC',
        'missing' => '_last'
    ]

DESCENDANT
""""""""""

.. code-block:: php

    'sort' => [
        'id' => 'DESC',
        'missing' => '_last'
    ]

Identifier
**********
:Apply: apply datatype 'keyword' on the 'identifier' field
:Normalizer: Lowercase normalizer

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'identifier' => 'prct-eb-1256'
    ]

Filtering
~~~~~~~~~
Operators
.........
All operators are the same as the Text field type except that the 'EMPTY' and 'NOT EMPTY' operators do not exists for this property.

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

    'must_not' => [
        'query_string' => [
            'default_field' => 'identifier',
            'query' => '*00*'
        ]
    ],
    'filter' => [
        'exists' => ['field' => 'identifier']
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

Sorting
~~~~~~~
Operators
.........

Whenever one wants to sort on the field 'identifier' or an attribute of type 'pim_catalog_identifier'. The sort query will be performed on the field 'identifier'.

ASCENDANT
"""""""""

.. code-block:: php

    'sort' => [
        'identifier' => 'ASC',
        'missing' => '_last'
    ]

DESCENDANT
""""""""""

.. code-block:: php

    'sort' => [
        'identifier' => 'DESC',
        'missing' => '_last'
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
:Specific field: original_filename

Same syntax than the ``contains`` but must be included in a ``must_not`` type instead of ``filter``.

.. code-block:: php

    'must_not' => [
        'query_string' => [
            'default_field' => 'values.an_image-media.<all_channels>.<all_locales>.original_filename',
            'query' => '*ziggy*'
        ]
    ],
    'filter' => [
        'exists' => ['field' => 'values.an_image-media.<all_channels>.<all_locales>'
    ]

Equals (=)
""""""""""
:Specific field: original_filename

.. code-block:: php

    'filter' => [
        'term' => [
            'values.an_image-media.<all_channels>.<all_locales>.original_filename' => 'akeneo.jpg'
        ]
    ]

Not Equals (!=)
"""""""""""""""
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

.. code-block:: php

    'must_not' => [
        'exists => [
            'field' => 'values.an_image-media.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""

.. code-block:: php

    'filter' => [
        'exists => [
            'field' => 'values.an_image-media.<all_channels>.<all_locales>'
        ]
    ]

Date
****
:Apply: pim_catalog_date attributes

Data model
~~~~~~~~~~

.. code-block:: yaml

    [
        'values' => [
            'publishedOn-date' => [
                '<all_channels>' => [
                    '<all_locales>' => '2015-02-24'
                ]
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........
Less than (<)
"""""""""""""

.. code-block:: php

    'range' => [
        'values.publishedOn-date.<all_channels>.<all_locales>' => [
            'lt' => '2015-02-26'
        ]
    ]


Equals (=)
""""""""""

.. code-block:: php

    'term' => [
        'values.publishedOn-date.<all_channels>.<all_locales>' => '2015-02-26'
    ]

NOT EQUAL (!=)
""""""""""""""

.. code-block:: php

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

BETWEEN
"""""""

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

.. code-block:: php

    'must_not' => [
        'range' => [
            'values.publishedOn-date.<all_channels>.<all_locales>' => [
                'gte' => '2017-03-22',
                'lte' => '2017-03-23'
            ],
        ]
    ],
    'filter' => [
        'exists' => 'values.publishedOn-date.<all_channels>.<all_locales>'
    ]

Greater than (>)
""""""""""""""""

.. code-block:: php

    'range' => [
        'values.publishedOn-date.<all_channels>.<all_locales>' => [
            'gt' => '2015-02-26'
        ]
    ]

EMPTY
"""""

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'values.publishedOn-date.<all_channels>.<all_locales>',
        ]
    ]

Sorting
~~~~~~~
Operators
.........
ASCENDANT
"""""""""

.. code-block:: php

    sort => [
        'values.publishedOn-date.<all_channels>.<all_locales>' => [
            'order'   => 'ASC',
            'missing' => '_last',
        ]
    ]

DESCENDANT
""""""""""

.. code-block:: php

    sort => [
        'values.publishedOn-date.<all_channels>.<all_locales>' => [
            'order'   => 'DESC',
            'missing' => '_last',
        ]
    ]

Decimal
*******
:Apply: pim_catalog_number attributes

Please note that number attributes must be indexed as a string to be captured by the dynamic mapping. This way, the PIM doesn't need to manage float or integer questions.

Data model
~~~~~~~~~~

.. code-block:: yaml

    [
        'values' => [
            'packet_count-decimal' => [
                '<all_channels>' => [
                    '<all_locales>' => '5.01992812'
                ]
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........
Less than (<)
"""""""""""""

.. code-block:: php

    'filter' => [
        'range' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => ['lt' => 10]
        ]
    ]

Less than or equals to (<=)
"""""""""""""""""""""""""""

.. code-block:: php

    'filter' => [
        'range' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => ['lte' => 10]
        ]
    ]

Equals (=)
""""""""""

.. code-block:: php

    'filter' => [
        'term' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => 5
        ]
    ]

Not Equal (!=)
""""""""""""""

.. code-block:: php

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


Greater than or equal to (>=)
"""""""""""""""""""""""""""""

.. code-block:: php

    'filter' => [
        'range' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => ['gte' => 10]
        ]
    ]

Greater than (>)
""""""""""""""""

.. code-block:: php

    'filter' => [
        'range' => [
            'values.packet_count-decimal.<all_channels>.<all_locales>' => ['gt' => 10]
        ]
    ]

EMPTY
"""""

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'values.packet_count-decimal.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.packet_count-decimal.<all_channels>.<all_locales>'
        ]
    ]


Sorting
~~~~~~~
Operators
.........
Ascendant
"""""""""

.. code-block:: php

    'sort' => [
        'values.packet_count-decimal.<all_channels>.<all_locales>' => [
            'order'   => 'ASC',
            'missing' => '_last'
        ]
    ]

Descendant
""""""""""

.. code-block:: php

    'sort' => [
        'values.packet_count-decimal.<all_channels>.<all_locales>' => [
            'order'   => 'DESC',
            'missing' => '_last'
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

.. code-block:: php

    'filter' => [
        'terms' => [
            'values.color-option.<all_channels>.<all_locales>' => ['red']
        ]
    ]

EMPTY
"""""

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'values.color-option.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.color-option.<all_channels>.<all_locales>'
        ]
    ]

NOT IN
""""""

.. code-block:: php

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

Sorting
~~~~~~~
Operators
.........
Ascendant
"""""""""

.. code-block:: php

    'sort' => [
        'values.color-option.<all_channels>.<all_locales>' => [
            'order'   => 'ASC',
            'missing' => '_last'
        ]
    ]

Descendant
""""""""""

.. code-block:: php

    'sort' => [
        'values.color-option.<all_channels>.<all_locales>' => [
            'order'   => 'DESC',
            'missing' => '_last'
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

.. code-block:: php

    'filter' => [
        'terms' => [
            'values.brand-reference_data_option.<all_channels>.<all_locales>' => ['acme']
        ]
    ]

EMPTY
"""""

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'values.brand-reference_data_option.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.brand-reference_data_option.<all_channels>.<all_locales>'
        ]
    ]

NOT IN
""""""

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
Sorting will be done on the localized label.

Operators
.........
ASCENDANT
"""""""""


.. code-block:: php

    'sort' => [
        'values.brand-reference_data_option.<all_channels>.<all_locales>' => [
            'order'   => 'asc',
            'missing' => '_last'
        ]
    ]

DESCENDANT
""""""""""

.. code-block:: php

    'sort' => [
        'values.brand-reference_data_option.<all_channels>.<all_locales>' => [
            'order'   => 'desc',
            'missing' => '_last'
        ]
    ]

Options
*******
:Apply: apply on the 'pim_catalog_multiselect' attributes

Data model
~~~~~~~~~~
.. code-block:: php

  'values' => [
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

.. code-block:: php

    'terms' => [
        'values.my-tags-options.mobile.fr_FR' => ['summer']
    ]

NOT IN
""""""

.. code-block:: php

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

IS EMPTY
""""""""

.. code-block:: php

    'exists' => [
        'field' => 'values.my-tags-options.mobile.fr_FR'
    ]

IS NOT EMPTY
""""""""""""

.. code-block:: php

    'exists' => [
        'field' => 'values.my-tags-options.mobile.fr_FR'
    ]

Sorting
~~~~~~~
Not supported on this attribute_type.

Reference data multi select
***************************

:Apply: pim_catalog_reference_data_multiselect attributes

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'values' => [
            'compatibility-reference_data_options' => [
                '<all_channels>' => [
                    '<all_locales>' => ['windows_os', 'linux']
                ]
            ]
        ]
    ]

Filtering
~~~~~~~~~
Operators
.........
IN
""

.. code-block:: php

    'filter' => [
        'terms' => [
            'values.compatibility-reference_data_options.<all_channels>.<all_locales>' => ['windows_os', 'mac_os']
        ]
    ]

EMPTY
"""""

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.compatibility-reference_data_options.<all_channels>.<all_locales>'
        ]
    ]

NOT EMPTY
"""""""""

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'values.compatibility-reference_data_options.<all_channels>.<all_locales>'
        ]
    ]

NOT IN
""""""

.. code-block:: php

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

Sorting
~~~~~~~
Not supported on this attribute_type.

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

All operators are identical to the one used on numbers except we filter on the `base_data` value. So the attribute path becomes:

.. code-block:: php

    'values.weight-metric.mobile.fr_FR.base_data'

Sorting
~~~~~~~
Operators
.........

All operators are identical to the one used on numbers except we filter on the `base_data` value. So the attribute path becomes:

.. code-block:: php

    'values.weight-metric.mobile.fr_FR.base_data'

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

The operators "EQUALS", "NOT EQUALS", "LOWER THAN", "LOWER OR EQUALS THAN", "GREATHER THAN" and "GREATER OR EQUALS THAN" are now deprecated in favor of more meaningful operators.
They are replaced respectively by:
    * "EQUALS ON AT LEAST ONE LOCALE"
    * "NOT EQUALS ON AT LEAST ONE LOCALE"
    * "LOWER THAN ON AT LEAST ONE LOCALE"
    * "LOWER OR EQUALS THAN ON AT LEAST ONE LOCALE"
    * "GREATER THAN ON AT LEAST ONE LOCALE"
    * "GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE"

IS EMPTY
~~~~~~~~

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'must_not' => [
                    'exists' => [
                        'field' => 'completeness'
                    ]
                ]
            ]
        ]
    ]

EQUALS ON AT LEAST ONE LOCALE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'bool' => [
                        'should' => [
                            ['term' => ['completeness.print.en_US' => 30]],
                            ['term' => ['completeness.print.fr_FR' => 30]]
                        ]
                    ]
                ]
            ]
        ]
    ]

NOT EQUALS ON AT LEAST ONE LOCALE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Example: product "SKU-001", for the channel "tablet", has the following complete ratios:
 * 50% for "en_US"
 * 50% for "fr_FR"
 * 50% for "it_IT"

If we look for the products where the completeness != 50 on the channel tablet, then, the product "SKU-001" should not be part of the results.
To achieve that, we look for
* MUST NOT (50% for "completeness.tablet.en_US" AND 50% for "completeness.tablet.fr_FR" AND 50% for "completeness.tablet.it_IT") AND
* EXISTS "completeness.tablet.en_US" AND
* EXISTS "completeness.tablet.fr_FR" AND
* EXISTS "completeness.tablet.it_IT"


:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'must_not' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'completeness.print.en_US' => 30,
                                'completeness.print.fr_FR' => 30,
                            ]
                        ]
                    ]
                ],
                'filter' => [
                    'exists' => [
                        'field' => 'completeness.print.en_US'
                    ],
                    'exists' => [
                        'field' => 'completeness.print.fr_FR'
                    ],
                ]
            ]
        ]
    ]

LOWER THAN ON AT LEAST ONE LOCALE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'bool' => [
                        'should' => [
                            ['range' => ['completeness.print.en_US' => ['lt' => 30]]],
                            ['range' => ['completeness.print.fr_FR' => ['lt' => 30]]]
                        ]
                    ]
                ]
            ]
        ]
    ]

LOWER OR EQUALS THAN ON AT LEAST ONE LOCALE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'bool' => [
                        'should' => [
                            ['range' => ['completeness.print.en_US' => ['lte' => 30]]],
                            ['range' => ['completeness.print.fr_FR' => ['lte' => 30]]]
                        ]
                    ]
                ]
            ]
        ]
    ]

GREATER THAN ON AT LEAST ONE LOCALE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'bool' => [
                        'should' => [
                            ['range' => ['completeness.print.en_US' => ['gt' => 30]]],
                            ['range' => ['completeness.print.fr_FR' => ['gt' => 30]]]
                        ]
                    ]
                ]
            ]
        ]
    ]

GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'bool' => [
                        'should' => [
                            ['range' => ['completeness.print.en_US' => ['gte' => 30]]],
                            ['range' => ['completeness.print.fr_FR' => ['gte' => 30]]]
                        ]
                    ]
                ]
            ]
        ]
    ]


LOWER THAN ON ON ALL LOCALES
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'range' => [
                        'completeness.print.en_US' => ['lt' => 30],
                        'completeness.print.fr_FR' => ['lt' => 30],
                    ]
                ]
            ]
        ]
    ]

LOWER OR EQUALS THAN ON ON ALL LOCALES
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'range' => [
                        'completeness.print.en_US' => ['lte' => 30],
                        'completeness.print.fr_FR' => ['lte' => 30],
                    ]
                ]
            ]
        ]
    ]

GREATER THAN ON ON ALL LOCALES
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'range' => [
                        'completeness.print.en_US' => ['gt' => 30],
                        'completeness.print.fr_FR' => ['gt' => 30],
                    ]
                ]
            ]
        ]
    ]

GREATER OR EQUALS THAN ON ON ALL LOCALES
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

:Type: filter

.. code-block:: php

    [
        'query' => [
            'bool' => [
                'filter' => [
                    'range' => [
                        'completeness.print.en_US' => ['gte' => 30],
                        'completeness.print.fr_FR' => ['gte' => 30],
                    ]
                ]
            ]
        ]
    ]


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

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'categories'
        ]
    ]

IN OR UNCLASSIFIED
~~~~~~~~~~~~~~~~~~

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

:Apply: apply datatype 'keyword' on the 'id' field

Unique ID of the product. This field is also used as the Elasticsearch ``"_id"`` field.

Data model
~~~~~~~~~~
.. code-block:: php

    [
        'id' => '4f3fcfec-2448-11e7-93ae-92361f002671'
    ]

Filtering
~~~~~~~~~
Operators
.........

Equals (=)
""""""""""

.. code-block:: php

    'filter' => [
        'term' => [
            'id' => '4f3fcfec-2448-11e7-93ae-92361f002671'
        ]
    ]

Not Equal (!=)
""""""""""""""

.. code-block:: php

    'must_not' => [
        'term' => [
            'id' => '4f3fcfec-2448-11e7-93ae-92361f002671'
        ]
    ],
    'filter' => [
        'exists' => [
            'field' => 'id'
        ]
    ]

In list
"""""""

.. code-block:: php

    'filter' => [
        'terms' => [
            'id' => ['4f3fcfec-2448-11e7-93ae-92361f002671', '5f61fd3c-2448-11e7-93ae-92361f002671']
        ]
    ]

Not In list
"""""""""""

.. code-block:: php

    'must_not' => [
        'terms' => [
            'id' => ['4f3fcfec-2448-11e7-93ae-92361f002671', '5f61fd3c-2448-11e7-93ae-92361f002671']
        ]
    ]

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

Family
******
Data model
~~~~~~~~~~
.. code-block:: php

    [
        'family' => 'familyA'
    ]

Sorting
~~~~~~~
Sorting is done on the localized label:

.. code-block:: php

    'sort' => [
        'family.label-en_US' => 'ASC',
        'mising' => '_last'
    ]

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

.. code-block:: php

    'filter' => [
        'terms' => [
            'groups' => ['groupA', 'groupB', 'groupC']
        ]
    ]

NOT IN
~~~~~~
:Type: must_not

.. code-block:: php

    'must_not' => [
        'terms' => [
            'groups' => ['groupA', 'groupB', 'groupC']
        ]
    ]

IS EMPTY
~~~~~~~~
:Type: must_not

.. code-block:: php

    'must_not' => [
        'exists' => [
            'field' => 'groups'
        ]
    ]

IS NOT EMPTY
~~~~~~~~~~~~
:Type: filter

.. code-block:: php

    'filter' => [
        'exists' => [
            'field' => 'groups'
        ]
    ]

Testing
-------
All queries above are (or should be) defined as integration tests under the namespace `Pim\Bundle\CatalogBundle\tests\integration\PQB`.
