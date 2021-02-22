Feature: Read a single product by applying rules
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed and correctly applied to the product

  Background:
    Given a "clothing" catalog configuration

  @integration-back
  Scenario: Successfully execute a rule with an "equals" condition
    Given the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:  set
            field: name
            value: My jacket
            locale: en_US
      """
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "my-jacket" should be "My jacket"

  @integration-back @purge-messenger
  Scenario: Successfully execute a rule with a "not equal" condition
    Given the following products:
      | sku         | family  |
      | my-jacket   | jackets |
      | my-cardigan | jackets |
    And the following product values:
      | product     | attribute   | value                  | locale | scope  |
      | my-jacket   | name        | White jacket           | en_US  |        |
      | my-jacket   | name        | Veste blanche          | fr_FR  |        |
      | my-jacket   | description | A stylish white jacket | en_US  | mobile |
      | my-cardigan | name        | Red cardigan           | en_US  |        |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: '!='
            value:    my-cardigan
        actions:
          - type:  set
            field: name
            value: My jacket
            locale: en_US
      """
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "my-jacket" should be "My jacket"
    And the en_US mobile description of "my-jacket" should be "A stylish white jacket"
    And the fr_FR unscoped name of "my-jacket" should be "Veste blanche"
    And the en_US unscoped name of "my-cardigan" should be "Red cardigan"
    And 1 event of type "product.updated" should have been raised

  @integration-back
  Scenario: Successfully execute a rule with a "not empty" condition
    Given the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        |                        | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
      | my-boot   | name        | White boot             | en_US  |        |
      | my-boot   | name        | Bootes blanches        | fr_FR  |        |
      | my-boot   | description | A stylish white boot   | en_US  | mobile |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    name
            operator: NOT EMPTY
            value:    null
            locale:   en_US
        actions:
          - type:  set
            field: name
            value: New name
            locale: en_US
      """
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "my-jacket" should be ""
    And the en_US unscoped name of "my-boot" should be "New name"

  @integration-back
  Scenario: Successfully execute a rule with a "starts with" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: STARTS WITH
            value:    my
        actions:
          - type:  set
            field: name
            value: My jacket
            locale: en_US
      """
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "my-jacket" should be "My jacket"

  @integration-back
  Scenario: Successfully execute a rule with an "ends with" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: STARTS WITH
            value:    my-j
        actions:
          - type:  set
            field: name
            value: My jacket
            locale: en_US
      """
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "my-jacket" should be "My jacket"

  @integration-back
  Scenario: Successfully execute a rule with a "contains" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: CONTAINS
            value:    ack
        actions:
          - type:  set
            field: name
            value: My jacket
            locale: en_US
      """
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "my-jacket" should be "My jacket"

  @integration-back
  Scenario: Successfully execute a rule with a "does not contain" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: DOES NOT CONTAIN
            value:    not
        actions:
          - type:  set
            field: name
            value: My jacket
            locale: en_US
      """
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "my-jacket" should be "My jacket"

  @integration-back
  Scenario: Successfully execute a rule with an "IN" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: IN
            value:
              - my-jacket
        actions:
          - type:  set
            field: name
            value: My jacket
            locale: en_US
      """
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "my-jacket" should be "My jacket"

  @integration-back
  Scenario: Successfully execute a rule with setter actions to update non empty values on all kind of fields
    Given the following products:
      | sku       | family  | name-fr_FR | weather_conditions | enabled | categories |
      | my-jacket | jackets | boot       | dry                | true    | jackets    |
    And the following product values:
      | product   | attribute          | value          | locale | scope  |
      | my-jacket | name               | White jacket   | en_US  |        |
      | my-jacket | handmade           | 0              |        |        |
      | my-jacket | release_date       | 2015-01-01     |        | mobile |
      | my-jacket | length             | 60 CENTIMETER  |        |        |
      | my-jacket | weather_conditions | wet,cold       |        |        |
      | my-jacket | number_in_stock    | 900            |        | mobile |
      | my-jacket | size               | M              |        |        |
      | my-jacket | price-USD          | 200            |        |        |
      | my-jacket | description        | Leather jacket | en_US  | mobile |
    And the following product rule definitions:
      """
      set_jacket:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:  set
            field: name
            value: Veste blanche
            locale: fr_FR
          - type:  set
            field: handmade
            value: true
          - type:  set
            field: release_date
            value: ""
            scope: mobile
          - type:  set
            field: datasheet
            value: %fixtures%/akeneo.txt
          - type:  set
            field: side_view
            value: %fixtures%/akeneo2.jpg
          - type:  set
            field: length
            value:
              amount: 50
              unit: CENTIMETER
          - type:  set
            field: weather_conditions
            value:
              - dry
              - hot
          - type:  set
            field: number_in_stock
            value: 8000
            scope: tablet
          - type:  set
            field: size
            value: L
          - type:  set
            field: price
            value:
              - amount: 180
                currency: EUR
          - type:  set
            field: description
            value: En cuir
            scope: tablet
            locale: fr_FR
          - type:  set
            field: enabled
            value: 0
          - type:  set
            field: categories
            value:
              - winter_top
              - tshirts
      """
    Then product "my-jacket" should be enabled
    And the category of the "my-jacket" product should be "jackets"
    And the product rule "set_jacket" is executed
    Then the product "my-jacket" should have the following values:
      | name-fr_FR               | Veste blanche      |
      | handmade                 | 1                  |
      | release_date-mobile      |                    |
      | datasheet                | akeneo             |
      | side_view                | akeneo2            |
      | length                   | 50.0000 CENTIMETER |
      | weather_conditions       | [dry], [hot]       |
      | number_in_stock-tablet   | 8000               |
      | size                     | L                  |
      | price-EUR                | 180.00             |
      | description-fr_FR-tablet | En cuir            |
    Then product "my-jacket" should be disabled
    And the category of the "my-jacket" product should be "winter_top, tshirts"

  @integration-back
  Scenario: Successfully execute a rule with copier actions to update non empty values on all kind of attributes
    Given the following attributes:
      | code            | label-en_US     | type                         | scopable | localizable | allowed_extensions | metric_family | default_metric_unit | group | decimals_allowed |
      | made_in_france  | Made in France  | pim_catalog_boolean          | 0        | 0           |                    |               |                     | other |                  |
      | report          | Report          | pim_catalog_file             | 0        | 0           | txt                |               |                     | other |                  |
      | climate         | Climate         | pim_catalog_multiselect      | 0        | 0           |                    |               |                     | other |                  |
      | promotion_price | Promotion price | pim_catalog_price_collection | 0        | 0           |                    |               |                     | other | 0                |
    And the family "jackets" has the attributes "made_in_france, report, climate, promotion_price, width"
    And the following products:
      | sku       | family  | weather_conditions |
      | my-jacket | jackets | dry                |
    And the following "climate" attribute options: hot, cold
    And the following product values:
      | product   | attribute          | value                  | locale | scope  |
      | my-jacket | handmade           | 1                      |        |        |
      | my-jacket | made_in_france     | 0                      |        |        |
      | my-jacket | release_date       | 2015-09-18             |        | mobile |
      | my-jacket | release_date       |                        |        | tablet |
      | my-jacket | datasheet          | %fixtures%/akeneo.txt  |        |        |
      | my-jacket | report             |                        |        |        |
      | my-jacket | side_view          | %fixtures%/akeneo2.jpg |        |        |
      | my-jacket | top_view           |                        |        |        |
      | my-jacket | length             | 55 CENTIMETER          |        |        |
      | my-jacket | width              |                        |        |        |
      | my-jacket | weather_conditions | hot,cold               |        |        |
      | my-jacket | climate            |                        |        |        |
      | my-jacket | number_in_stock    | 800                    |        | mobile |
      | my-jacket | number_in_stock    |                        |        | tablet |
      | my-jacket | main_color         | white                  |        |        |
      | my-jacket | secondary_color    |                        |        |        |
      | my-jacket | name               | White jacket           | en_US  |        |
      | my-jacket | name               |                        | fr_FR  |        |
      | my-jacket | description        | A stylish white jacket | en_US  | mobile |
      | my-jacket | description        |                        | fr_FR  | tablet |
    And the following product rule definitions:
      """
      copy_jacket:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:       copy
            from_field: handmade
            to_field:   made_in_france
          - type:       copy
            from_field: release_date
            to_field:   release_date
            from_scope: mobile
            to_scope:   tablet
          - type:       copy
            from_field: datasheet
            to_field:   report
          - type:       copy
            from_field: side_view
            to_field:   top_view
          - type:       copy
            from_field: length
            to_field:   width
          - type:       copy
            from_field: weather_conditions
            to_field:   climate
          - type:       copy
            from_field: number_in_stock
            to_field:   number_in_stock
            from_scope: mobile
            to_scope:   tablet
          - type:  copy
            from_field: main_color
            to_field: secondary_color
          - type:       copy
            from_field: name
            to_field:   name
            from_locale: en_US
            to_locale:   fr_FR
          - type:       copy
            from_field: description
            to_field:   description
            from_locale: en_US
            to_locale:   fr_FR
            from_scope:  mobile
            to_scope:    tablet
      """
    And the product rule "copy_jacket" is executed
    Then the product "my-jacket" should have the following values:
      | handmade                 | 1                      |
      | made_in_france           | 1                      |
      | release_date-mobile      | 2015-09-18             |
      | release_date-tablet      | 2015-09-18             |
      | datasheet                | akeneo                 |
      | report                   | akeneo                 |
      | side_view                | akeneo2                |
      | top_view                 | akeneo2                |
      | length                   | 55.0000 CENTIMETER     |
      | width                    | 55.0000 CENTIMETER     |
      | weather_conditions       | [cold], [hot]          |
      | climate                  | [cold], [hot]          |
      | number_in_stock-mobile   | 800                    |
      | number_in_stock-tablet   | 800                    |
      | main_color               | white                  |
      | secondary_color          | white                  |
      | name-en_US               | White jacket           |
      | name-fr_FR               | White jacket           |
      | description-en_US-mobile | A stylish white jacket |
      | description-fr_FR-tablet | A stylish white jacket |

  @integration-back
  Scenario: Successfully execute a rule with copier actions to update empty values on all kind of attributes
    Given the following attributes:
      | code            | label-en_US     | type                         | scopable | localizable | allowed_extensions | metric_family | default_metric_unit | group | decimals_allowed |
      | made_in_france  | Made in France  | pim_catalog_boolean          | 0        | 0           |                    |               |                     | other |                  |
      | report          | Report          | pim_catalog_file             | 0        | 0           | txt                |               |                     | other |                  |
      | climate         | Climate         | pim_catalog_multiselect      | 0        | 0           |                    |               |                     | other |                  |
      | promotion_price | Promotion price | pim_catalog_price_collection | 0        | 0           |                    |               |                     | other | 0                |
    And the family "jackets" has the attributes "made_in_france, report, climate, promotion_price, width"
    And the following products:
      | sku       | family  | weather_conditions |
      | my-jacket | jackets | dry                |
    And the following "climate" attribute options: hot, cold
    And the following product values:
      | product   | attribute          | value                  | locale | scope  |
      | my-jacket | handmade           |                        |        |        |
      | my-jacket | made_in_france     | 0                      |        |        |
      | my-jacket | release_date       |                        |        | mobile |
      | my-jacket | release_date       | 2015-09-18             |        | tablet |
      | my-jacket | datasheet          |                        |        |        |
      | my-jacket | report             | %fixtures%/akeneo.txt  |        |        |
      | my-jacket | side_view          |                        |        |        |
      | my-jacket | top_view           | %fixtures%/akeneo2.jpg |        |        |
      | my-jacket | weather_conditions |                        |        |        |
      | my-jacket | climate            | hot,cold               |        |        |
      | my-jacket | number_in_stock    |                        |        | mobile |
      | my-jacket | number_in_stock    | 800                    |        | tablet |
      | my-jacket | main_color         |                        |        |        |
      | my-jacket | secondary_color    | white                  |        |        |
      | my-jacket | name               |                        | en_US  |        |
      | my-jacket | name               | White jacket           | fr_FR  |        |
      | my-jacket | description        |                        | en_US  | mobile |
      | my-jacket | description        | A stylish white jacket | fr_FR  | tablet |
      | my-jacket | length             |                        |        |        |
      | my-jacket | width              | 55 CENTIMETER          |        |        |
    And the following product rule definitions:
      """
      copy_jacket:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:       copy
            from_field: handmade
            to_field:   made_in_france
          - type:       copy
            from_field: release_date
            to_field:   release_date
            from_scope: mobile
            to_scope:   tablet
          - type:       copy
            from_field: datasheet
            to_field:   report
          - type:       copy
            from_field: side_view
            to_field:   top_view
          - type:       copy
            from_field: length
            to_field:   width
          - type:       copy
            from_field: weather_conditions
            to_field:   climate
          - type:       copy
            from_field: number_in_stock
            to_field:   number_in_stock
            from_scope: mobile
            to_scope:   tablet
          - type:       copy
            from_field: main_color
            to_field:   secondary_color
          - type:        copy
            from_field: name
            to_field:   name
            from_locale: en_US
            to_locale:   fr_FR
          - type:       copy
            from_field: description
            to_field:   description
            from_locale: en_US
            to_locale:   fr_FR
            from_scope:  mobile
            to_scope:    tablet
      """
    And the product rule "copy_jacket" is executed
    Then the product "my-jacket" should have the following values:
      | handmade                 |           |
      | made_in_france           |           |
      | release_date-mobile      |           |
      | release_date-tablet      |           |
      | datasheet                | **empty** |
      | report                   | **empty** |
      | side_view                | **empty** |
      | top_view                 | **empty** |
      | length                   |           |
      | width                    |           |
      | weather_conditions       |           |
      | climate                  |           |
      | number_in_stock-mobile   |           |
      | number_in_stock-tablet   |           |
      | main_color               |           |
      | secondary_color          |           |
      | name-en_US               |           |
      | name-fr_FR               |           |
      | description-en_US-mobile |           |
      | description-fr_FR-tablet |           |

  @integration-back
  Scenario: Successfully execute a rule with adder actions to update non empty values on all kind of fields
    Given the following products:
      | sku       | family  | categories |
      | my-jacket | jackets | jackets    |
    And the following product values:
      | product   | attribute          | value    | locale | scope |
      | my-jacket | weather_conditions | wet,cold |        |       |
    And the following product rule definitions:
      """
      rule_sku_jacket:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:  add
            field: weather_conditions
            items:
              - dry
              - hot
            locale: null
            scope: null
          - type:  add
            field: categories
            items:
              - tshirts
      """
    And the category of the "my-jacket" product should be "jackets"
    And the product rule "rule_sku_jacket" is executed
    Then the product "my-jacket" should have the following values:
      | weather_conditions | [cold], [dry], [hot], [wet] |
    And the category of the "my-jacket" product should be "jackets, tshirts"

  @integration-back
  Scenario: Successfully execute a rule with an "equals" condition
    Given the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    Then the completeness for the product "my-jacket" should be:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | de_DE  | warning | 8              | 11%   |
      | tablet  | en_US  | warning | 7              | 22%   |
      | tablet  | fr_FR  | warning | 7              | 22%   |
      | mobile  | de_DE  | warning | 4              | 20%   |
      | mobile  | en_US  | warning | 3              | 40%   |
      | mobile  | fr_FR  | warning | 3              | 40%   |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:  set
            field: description
            value: My jacket
            locale: en_US
            scope: tablet
      """
    When the product rule "set_name" is executed
    Then the completeness for the product "my-jacket" should be:
      | channel | locale | state   | missing_values | ratio |
      | tablet  | de_DE  | warning | 8              | 11%   |
      | tablet  | en_US  | warning | 6              | 33%   |
      | tablet  | fr_FR  | warning | 7              | 22%   |
      | mobile  | de_DE  | warning | 4              | 20%   |
      | mobile  | en_US  | warning | 3              | 40%   |
      | mobile  | fr_FR  | warning | 3              | 40%   |

  @integration-back
  Scenario: Successfully execute a rule with a "remove" action on a single category
    Given the following products:
      | sku       | family  | categories                                  | enabled |
      | my-jacket | jackets | 2014_collection, summer_collection, jackets | no      |
    And the following product rule definitions:
      """
      rule_remove_category_jacket:
        priority: 10
        conditions:
          - field:    family
            operator: IN
            value:
              - jackets
          - field:    enabled
            operator: =
            value:    false
        actions:
          - type:  remove
            field: categories
            items:
              - 2014_collection
            include_children: false
      """
    When the product rule "rule_remove_category_jacket" is executed
    Then the categories of the "my-jacket" product should be "summer_collection, jackets"

  @integration-back
  Scenario: Successfully execute a rule with a "remove" action on a category and its children
    Given the following products:
      | sku       | family  | categories                                                     | enabled |
      | my-jacket | jackets | 2014_collection, summer_collection, winter_collection, jackets | no      |
    And the following product rule definitions:
      """
      rule_remove_category_jacket:
        priority: 10
        conditions:
          - field:    family
            operator: IN
            value:
              - jackets
          - field:    enabled
            operator: =
            value:    false
        actions:
          - type:  remove
            field: categories
            items:
              - summer_collection
            include_children: true
      """
    When the product rule "rule_remove_category_jacket" is executed
    Then the categories of the "my-jacket" product should be "2014_collection, winter_collection"
