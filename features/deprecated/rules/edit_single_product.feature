Feature: Read a single product by applying rules
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed and correctly applied to the product

  Background:
    Given a "clothing" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"

  @deprecated @javascript
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
          - type:  set_value
            field: name
            value: My jacket
            locale: en_US
      """
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  @deprecated @javascript
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
          - type:  set_value
            field: name
            value: My jacket
            locale: en_US
      """
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  @deprecated @javascript
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
            operator: ENDS WITH
            value:    ket
        actions:
          - type:  set_value
            field: name
            value: My jacket
            locale: en_US
      """
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  @deprecated @javascript
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
          - type:  set_value
            field: name
            value: My jacket
            locale: en_US
      """
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  @deprecated @javascript
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
          - type:  set_value
            field: name
            value: My jacket
            locale: en_US
      """
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  @deprecated @javascript
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
          - type:  set_value
            field: name
            value: My jacket
            locale: en_US
      """
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  @deprecated
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
          - type:  set_value
            field: name
            value: Veste blanche
            locale: fr_FR
          - type:  set_value
            field: handmade
            value: 1
          - type:  set_value
            field: release_date
            value: "2015-08-08"
            scope: tablet
          - type:  set_value
            field: datasheet
            value:
              originalFilename: akeneo.txt
              filePath: %fixtures%/akeneo.txt
          - type:  set_value
            field: side_view
            value:
              originalFilename: akeneo2.jpg
              filePath: %fixtures%/akeneo2.jpg
          - type:  set_value
            field: length
            value:
              data: 50
              unit: CENTIMETER
          - type:  set_value
            field: weather_conditions
            value:
              - dry
              - hot
          - type:  set_value
            field: number_in_stock
            value: 8000
            scope: tablet
          - type:  set_value
            field: size
            value: L
          - type:  set_value
            field: price
            value:
              - data: 180
                currency: EUR
          - type:  set_value
            field: description
            value: En cuir
            scope: tablet
            locale: fr_FR
          - type:  set_value
            field: enabled
            value: 0
          - type:  set_value
            field: categories
            value:
              - winter_top
              - tshirts
      """
    Then product "my-jacket" should be enabled
    And the category of "my-jacket" should be "jackets"
    Given the product rule "set_jacket" is executed
    Then the product "my-jacket" should have the following values:
      | name-fr_FR               | Veste blanche      |
      | handmade                 | 1                  |
      | release_date-tablet      | 2015-08-08         |
      | datasheet                | akeneo             |
      | side_view                | akeneo2            |
      | length                   | 50.0000 CENTIMETER |
      | weather_conditions       | [dry], [hot]       |
      | number_in_stock-tablet   | 8000               |
      | size                     | [L]                |
      | price-EUR                | 180.00             |
      | description-fr_FR-tablet | En cuir            |
    Then product "my-jacket" should be disabled
    And the category of "my-jacket" should be "winter_top, tshirts"

  @deprecated
  Scenario: Successfully execute a rule with copier actions to update non empty values on all kind of attributes
    Given the following attributes:
      | code            | label           | type        | scopable | localizable | allowedExtensions | metric_family | default_metric_unit |
      | made_in_france  | Made in France  | boolean     | no       | no          |                   |               |                     |
      | report          | Report          | file        | no       | no          | txt               |               |                     |
      | climate         | Climate         | multiselect | no       | no          |                   |               |                     |
      | promotion_price | Promotion price | prices      | no       | no          |                   |               |                     |
    And the following products:
      | sku       | family  | weather_conditions |
      | my-jacket | jackets | dry                |
    And the following "climate" attribute options: Hot and Cold
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
      | my-jacket | weather_conditions | Hot,Cold               |        |        |
      | my-jacket | climate            |                        |        |        |
      | my-jacket | number_in_stock    | 800                    |        | mobile |
      | my-jacket | number_in_stock    |                        |        | tablet |
      | my-jacket | main_color         | White                  |        |        |
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
          - type:  copy_value
            from_field: handmade
            to_field: made_in_france
          - type:  copy_value
            from_field: release_date
            to_field: release_date
            from_scope:  mobile
            to_scope:    tablet
          - type:  copy_value
            from_field: datasheet
            to_field: report
          - type:  copy_value
            from_field: side_view
            to_field: top_view
          - type:  copy_value
            from_field: length
            to_field: width
          - type:  copy_value
            from_field: weather_conditions
            to_field: climate
          - type:  copy_value
            from_field: number_in_stock
            to_field: number_in_stock
            from_scope:  mobile
            to_scope:    tablet
          - type:  copy_value
            from_field: main_color
            to_field: secondary_color
          - type:        copy_value
            from_field:  name
            to_field:    name
            from_locale: en_US
            to_locale:   fr_FR
          - type:        copy_value
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   fr_FR
            from_scope:  mobile
            to_scope:    tablet
      """
    Given the product rule "copy_jacket" is executed
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
      | weather_conditions       | [hot], [cold]          |
      | climate                  | [hot], [cold]          |
      | number_in_stock-mobile   | 800.00                 |
      | number_in_stock-tablet   | 800.00                 |
      | main_color               | [white]                |
      | secondary_color          | [white]                |
      | name-en_US               | White jacket           |
      | name-fr_FR               | White jacket           |
      | description-en_US-mobile | A stylish white jacket |
      | description-fr_FR-tablet | A stylish white jacket |

  @deprecated
  Scenario: Successfully execute a rule with copier actions to update empty values on all kind of attributes
    Given the following attributes:
      | code            | label           | type        | scopable | localizable | allowedExtensions | metric_family | default_metric_unit |
      | made_in_france  | Made in France  | boolean     | no       | no          |                   |               |                     |
      | report          | Report          | file        | no       | no          | txt               |               |                     |
      | climate         | Climate         | multiselect | no       | no          |                   |               |                     |
      | promotion_price | Promotion price | prices      | no       | no          |                   |               |                     |
    And the following products:
      | sku       | family  | weather_conditions |
      | my-jacket | jackets | dry                |
    And the following "climate" attribute options: Hot and Cold
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
      | my-jacket | climate            | Hot,Cold               |        |        |
      | my-jacket | number_in_stock    |                        |        | mobile |
      | my-jacket | number_in_stock    | 800                    |        | tablet |
      | my-jacket | main_color         |                        |        |        |
      | my-jacket | secondary_color    | White                  |        |        |
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
          - type:  copy_value
            from_field: handmade
            to_field: made_in_france
          - type:  copy_value
            from_field: release_date
            to_field: release_date
            from_scope:  mobile
            to_scope:    tablet
          - type:  copy_value
            from_field: datasheet
            to_field: report
          - type:  copy_value
            from_field: side_view
            to_field: top_view
          - type:  copy_value
            from_field: length
            to_field: width
          - type:  copy_value
            from_field: weather_conditions
            to_field: climate
          - type:  copy_value
            from_field: number_in_stock
            to_field: number_in_stock
            from_scope:  mobile
            to_scope:    tablet
          - type:  copy_value
            from_field: main_color
            to_field: secondary_color
          - type:        copy_value
            from_field:  name
            to_field:    name
            from_locale: en_US
            to_locale:   fr_FR
          - type:        copy_value
            from_field:  description
            to_field:    description
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

  @deprecated
  Scenario: Successfully execute a rule with adder actions to update non empty values on all kind of fields
    Given the following products:
      | sku       | family  | categories |
      | my-jacket | jackets | jackets    |
    And the following product values:
      | product   | attribute          | value          | locale | scope  |
      | my-jacket | weather_conditions | wet,cold       |        |        |
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
            data:
              - dry
              - hot
          - type:  add
            field: categories
            data:
              - tshirts
      """
    And the category of "my-jacket" should be "jackets"
    Given the product rule "rule_sku_jacket" is executed
    Then the product "my-jacket" should have the following values:
      | weather_conditions       | [dry], [wet], [hot], [cold] |
    And the category of "my-jacket" should be "jackets, tshirts"
