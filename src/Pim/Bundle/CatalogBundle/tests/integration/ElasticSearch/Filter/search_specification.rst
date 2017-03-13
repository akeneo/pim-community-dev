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

For example:
 - description-text: the ``-text`` suffix is applied, meaning that we must apply a specific analyzer for a text area attribute.


List of suffix and their mapping to Akeneo
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

===============================   ==========================
Akeneo attribute type             Elasticsearch field suffix
===============================   ==========================
 pim_catalog_identifier            -varchar
 pim_catalog_text                  -varchar
 pim_catalog_textarea              -text
 pim_catalog_metric                -metric
 pim_catalog_boolean               -bool
 pim_catalog_simpleselect          -option
 pim_catalog_number                -number
 pim_catalog_multiselect           -options
 pim_catalog_date                  -date
 pim_catalog_price_collection      -prices
 pim_catalog_image                 -media
 pim_catalog_file                  -media
===============================   ==========================

Common elements
***************
Naming
~~~~~~
 - Elasticsearch fields for attribute follow this naming scheme:

``attribute_code-backend_type.channel.locale.es_suffix``

- When the attribute is not localizable: ``locale`` becomes ``<all_locales>``
- When the attribute is not scopable: ``channel`` becomes ``<all_channels>``

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
                        'name-text' => 'Tshirt',
                    ],
                    'prefix' => [
                        'name-text' => 'Tshirt'
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
                        'description-text-en_US-mobile' => '30 pages'

                    ],
                    'match_phrase' => [
                            'name-text' => "canon"
                    ],
                    'prefix' => [
                        'name-text' => 'Tshirt'
                    ],
                    'term' => [
                        'price-prices' => 30
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
        'name-varchar' => "asc"
    ]

Sorting and tokenization
........................
Tokenized fields cannot be used for sorting as they will generate wrong results (see http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/multi-fields.html).

For those fields (mainly string fields), a multi-fields must be created with the untokenized value stored in a ``raw`` subfield.

In this case, the sort becomes:

.. code-block:: php

    'sort' => [
        'name-text.raw' => 'asc'
    ]

Text area
*********

:Apply: pim_catalog_textarea attributes
:Analyzer: HTML char filter + standard tokenizer + lowercase token filter

    Other fields analyzer:
     - raw: Keyword datatype + non-tokenized (Keyword Tokenizer) + lower case token filter

Data model
~~~~~~~~~~
.. code-block:: yaml

  my_description-text-fr_FR-mobile: 'My description'


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
            'default_field' => 'description-text.raw',
            'query' => "My*"
        ]
    ]

Note: All spaces must be escaped (with ``\\``) to prevent interpretation as separator. This applies on all query using a query_string.


Example:

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'description-text.raw',
            'query' => 'My\\ description*'
        ]
    ]


CONTAINS
""""""""
:Specific field: raw

.. code-block:: php

    'filter' => [
        'query_string' => [
            'default_field' => 'description-text.raw',
            'query' => 'cool\\ product'
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
                'default_field' => 'description-text.raw',
                'query' => 'cool\\ product'
            ]
        ],
        'filter' => [
            'exists' => ['field' => 'description-text.raw'
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
            'description-text.raw' => 'My full lookup text'
        ]
    ]

EMPTY
"""""
:Type: filter

.. code-block:: php

    'must_not' => [
        'exists => [
            'field' => 'description-text'
        ]
    ]

Text
****

:Apply: pim_catalog_text attributes
:Analyzer: keyword tokenizer + lowercase token filter

Data model
~~~~~~~~~~
.. code-block:: php

  name-varchar: "My product name"

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
:Apply: pim_catalog_identifier attribute
:Analyzer: same as text

Data model
~~~~~~~~~~
.. code-block:: yaml

  sku-ident: "PRCT-1256"

Filtering
~~~~~~~~~

Operators
.........
All operators are the same as the Text field type.

Media
*****
:Apply:
  pim_catalog_image and pim_catalog_file attributes

Data model
~~~~~~~~~~
.. code-block:: yaml

  my_image-media: "/images/test-image.jpg"

Filtering
~~~~~~~~~
Operators
.........

For STARTS WITH, ENDS WITH, CONTAINS, DOES NOT CONTAIN and =, same as identifier

EMPTY
"""""
:Type: filter

.. code-block:: yaml

    missing:
        field: "my_image-media"

Date
****
:Apply:
  pim_catalog_date attributes

Data model
~~~~~~~~~~
::

  "updated-date":"2015-02-24"

Filtering
~~~~~~~~~
Operators
.........
Less than (<)
"""""""""""""
:Type: filter

.. code-block:: yaml

    range:
        updated-date:
            lt : "2015-02-26"

Equals (=)
""""""""""
:Type: filter

.. code-block:: yaml

    term:
        updated-date:"2015-02-26"

BETWEEN
"""""""
:Type: filter

.. code-block:: yaml

    range:
        updated-date:
            lte: "2015-02-26"
            gte: "2015-02-21"

NOT BETWEEN
"""""""""""
:Type: filter

Same as the BETWEEN filter but in a ``must_not`` occured type

Greater than (>)
""""""""""""""""
:Type: filter

.. code-block:: yaml

    range:
        updated-date:
            gt : "2015-02-21"

EMPTY
"""""
:Type: filter

.. code-block:: yaml

    missing:
        field: "updated_date"


Number
******
:Apply:
 pim_catalog_number attributes

Please note that number attributes must be sent as string to be captured by the dynamic mapping. This way, the PIM doesn't need to be manage float or integer questions.


Data model
~~~~~~~~~~
.. code-block:: yaml

  packet_count-number: 5

Filtering
~~~~~~~~~
Operators
.........
Less than (<)
"""""""""""""
:Type: filter

.. code-block:: yaml

    range:
        packet_count-number:
            lt: 10

Less than or equals to (<=)
"""""""""""""""""""""""""""
:Type: filter

.. code-block:: yaml

    range:
        packet_count-number:
            lte: 11

Equals (=)
""""""""""
:Type: filter

.. code-block:: yaml

    term:
        packet_count-number: 5

Greater than or equal to (>=)
"""""""""""""""""""""""""""""
:Type: filter

.. code-block:: yaml

    range:
        packet_count-number:
            gte: 3

Greater than (>)
""""""""""""""""
:Type: filter

.. code-block:: yaml

    range:
        packet_count-number:
            gt: 4

EMPTY
"""""
:Type: filter

.. code-block:: yaml

    missing:
       field: "packet_count"

Option
******
:Apply: pim_catalog_simpleselect attributes

Data model
~~~~~~~~~~
.. code-block:: yaml

  color-option
    id:5
    label-en_US:"Red"
    label-fr_FR:"Rouge"

Filtering
~~~~~~~~~
Operators
.........
IN
""
:Type: filter

.. code-block:: yaml

    terms:
        color-option.id: [5, 6, 7]

EMPTY
"""""
:Type: filter

.. code-block:: yaml

    missing:
       field: "color-option"


Sorting
~~~~~~~
Sorting will be done on the localized label:

.. code-block:: yaml

    sort:
        color-option.label-en_US: asc

Simple select reference data
****************************
:Apply: pim_reference_data_simpleselect attributes

Data model
~~~~~~~~~~
.. code-block:: yaml

  brand-rd_option
    id:5
    code: "acme"

Filtering
~~~~~~~~~
Operators
.........
IN
""
:Type: filter

.. code-block:: yaml

    terms:
        brand-rd_option.id: [5, 6, 7]

EMPTY
"""""
:Type: filter

.. code-block:: yaml

    missing:
       field: "brand-rd_option"


Sorting
~~~~~~~
Sorting will be done on the localized label:

.. code-block:: yaml

    sort:
        brand-rd_option.code: asc

Options
*******
:Apply: pim_catalog_multiselect attributes

Data model
~~~~~~~~~~
.. code-block:: yaml

  compatibility-options:
    -
          id:2
          label-en_US:"Windows OS"
          label-fr_FR:"Système Windows"
    -
          id:4
          label-en_US:"MacOSX OS"
          label-fr_FR:"Système MacOSX"

Filtering
~~~~~~~~~
Operators
.........

IN
""
:Type: filter

.. code-block:: yaml

    terms:
        compatibility-options.id : [5, 6, 7]

EMPTY
"""""
:Type: filter

.. code-block:: yaml

    missing:
        field: "compatibility-options"

Sorting
~~~~~~~
Not supported on that attribute_type

Reference data multi select
***************************

:Apply: pim_catalog_reference_data_multiselect attributes

Data model
~~~~~~~~~~
.. code-block:: yaml

  compatibility-rd_options:
    -
          id:2
          code:"windows_os"
    -
          id:4
          code: "linux"

Filtering
~~~~~~~~~
Operators
.........

IN
""
:Type: filter

.. code-block:: yaml

    terms:
        compatibility-rd_options.id : [5, 6, 7]

EMPTY
"""""
:Type: filter

.. code-block:: yaml

    missing:
        field: "compatibility-rd_options"

Sorting
~~~~~~~
Not supported on that attribute_type

Metric
******
:Apply: pim_catalog_metric attributes

In case of metric, only the data converted to the default metric unit of the family
must be indexed.

Data model
~~~~~~~~~~
.. code-block:: yaml

    weight_metric: 10.5

Filtering
~~~~~~~~~
Operators
.........
All operators are identical to the one used on numbers

Boolean
*******
:Apply: pim_catalog_boolean attributes and 'enabled' field

Data model
~~~~~~~~~~
.. code-block:: yaml

    enabled_bool: true

Filtering
~~~~~~~~~
Operators
.........
Equals (=)
~~~~~~~~~~
:Type: filter

.. code-block:: yaml

    term:
        enabled_bool: true

Completeness
************
:Apply: 'completenesses' field

Data model
~~~~~~~~~~
.. code-block:: yaml

    completenesses:
        print:
          en_US-number: 100
          fr_FR-number: 89
      ecommerce:
          fr_FR-number: 79
          en_US-number: 85

Filtering
~~~~~~~~~
Operators
.........
All operators and syntax that apply on number apply as well on completeness, but by providing
the full path to the targeted completeness.

Example with the ``>`` operator:

.. code-block:: yaml

    range:
        completenesses.print.en_US-number:
            gt: 4

Category
********
:Apply: apply on 'categories' field

Data model
~~~~~~~~~~
.. code-block:: yaml

  categories: [1, 5, 8, 9]

Filtering
~~~~~~~~~
Operators
.........
IN
~~
:Type: filter

.. code-block:: yaml

    terms:
        categories: [5, 9]

NOT IN
~~~~~~
:Type: filter

Same as ``IN``, but with ``must_not`` occured type instead of ``must``

UNCLASSIFIED
~~~~~~~~~~~~
:Type: filter

.. code-block:: yaml

    missing:
        field: "categories"

IN OR UNCLASSIFIED
~~~~~~~~~~~~~~~~~~
:Type: filter

We use the ``should`` occured type to join both conditions on a ``bool`` filter

.. code-block:: yaml

    bool:
        should:
            -
                terms:
                    categories: [1, 4]
            -
                missing:
                    field: "categories"
        minimum_should_match: 1

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

Data model
~~~~~~~~~~
.. code-block:: yaml

    price-prices:
        USD-number: 125
        EUR-number: 110

Filtering
~~~~~~~~~
Same operators than ``number`` apply, but by using the full path to the price with its currency.

Example for the ``>`` operator:
::

.. code-block:: yaml

range:
        price-prices.USD-number:
            gt: 100

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
:Apply: "family" field

Data model
~~~~~~~~~~
.. code-block:: yaml

    family:
        id: 5
        label-en_US: "My family"
        label-fr_FR: "Ma famille"

Filtering
~~~~~~~~~
Operators
.........
IN
~~
:Type: filter

.. code-block:: yaml

    terms:
        family.id: [5, 6 7]

Sorting
~~~~~~~
Sorting is done on the localized label:

.. code-block:: yaml

    sort:
        family.label-en_US: "asc"

Groups
******
:Apply: "groups" field

Data model
~~~~~~~~~~
.. code-block:: yaml

    groups: [1, 5, 8]

Filtering
~~~~~~~~~
Operators
.........
IN
~~
:Type: filter

.. code-block:: yaml

    terms:
        groups.id: [5, 6 7]

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
