Feature: Read a single product with applied rules
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed and correctly applied to the product

  Background:
    Given a "clothing" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"

  Scenario: Successfully execute a rule with an "equals" condition
    Given the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator | value     |
      | set_name | sku   | =        | my-jacket |
    And the following product rule setter actions:
      | rule     | field | value     | locale |
      | set_name | name  | My jacket | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  Scenario: Successfully execute a rule with a "starts with" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator    | value |
      | set_name | sku   | STARTS WITH | my    |
    And the following product rule setter actions:
      | rule     | field | value     | locale |
      | set_name | name  | My jacket | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  Scenario: Successfully execute a rule with an "ends with" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator  | value |
      | set_name | sku   | ENDS WITH | ket   |
    And the following product rule setter actions:
      | rule     | field | value     | locale |
      | set_name | name  | My jacket | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  Scenario: Successfully execute a rule with a "contains" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator | value |
      | set_name | sku   | CONTAINS | ack   |
    And the following product rule setter actions:
      | rule     | field | value     | locale |
      | set_name | name  | My jacket | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  Scenario: Successfully execute a rule with a "does not contain" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator         | value |
      | set_name | sku   | DOES NOT CONTAIN | not   |
    And the following product rule setter actions:
      | rule     | field | value     | locale |
      | set_name | name  | My jacket | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  Scenario: Successfully execute a rule with an "IN" condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-jacket | jackets | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator | value     |
      | set_name | sku   | IN       | my-jacket |
    And the following product rule setter actions:
      | rule     | field | value     | locale |
      | set_name | name  | My jacket | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-jacket" product page
    Then the product Name should be "My jacket"

  Scenario: Successfully execute a rule with setter actions to update non empty values on all kind of attributes
    Given the following products:
      | sku       | family  | name-fr_FR | weather_conditions |
      | my-jacket | jackets | boot       | dry                |
    And the following product values:
      | product   | attribute          | value          | locale | scope  |
      | my-jacket | name               | White jacket   | en_US  |        |
      | my-jacket | handmade           | no             |        |        |
      | my-jacket | release_date       | 2015-01-01     |        | mobile |
      | my-jacket | length             | 60 CENTIMETER  |        |        |
      | my-jacket | weather_conditions | wet,cold       |        |        |
      | my-jacket | number_in_stock    | 900            |        | mobile |
      | my-jacket | size               | M              | en_US  |        |
      | my-jacket | price-USD          | 200            |        |        |
      | my-jacket | description        | Leather jacket | en_US  | mobile |
    And the following product rules:
      | code            | priority |
      | rule_sku_jacket | 10       |
    And the following product rule conditions:
      | rule            | field | operator | value     |
      | rule_sku_jacket | sku   | =        | my-jacket |
    And the following product rule setter actions:
      | rule            | field              | locale | scope  | value                                                  |
      | rule_sku_jacket | name               | fr_FR  |        | Veste blanche                                          |
      | rule_sku_jacket | handmade           |        |        | 1                                                      |
      | rule_sku_jacket | release_date       |        | tablet | 2015-08-08                                             |
      | rule_sku_jacket | datasheet          |        |        | akeneo,../../../features/Context/fixtures/akeneo.txt   |
      | rule_sku_jacket | side_view          |        |        | akeneo2,../../../features/Context/fixtures/akeneo2.jpg |
      | rule_sku_jacket | length             |        |        | 50,CENTIMETER                                          |
      | rule_sku_jacket | weather_conditions |        |        | dry,hot                                                |
      | rule_sku_jacket | number_in_stock    |        | tablet | 8000                                                   |
      | rule_sku_jacket | size               |        |        | L                                                      |
      | rule_sku_jacket | price              |        |        | 180,EUR                                                |
      | rule_sku_jacket | description        | fr_FR  | tablet | En cuir                                                |
    Given the product rule "rule_sku_jacket" is executed
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

  Scenario: Successfully execute a rule with copier actions to update non empty values on all kind of attributes
    Given the following products:
      | sku       | family  | weather_conditions |
      | my-jacket | jackets | dry                |
    And the following attributes:
      | code            | label           | type        | scopable | localizable | allowedExtensions | metric_family | default_metric_unit |
      | made_in_france  | Made in France  | boolean     | no       | no          |                   |               |                     |
      | report          | Report          | file        | no       | no          | txt               |               |                     |
      | climate         | Climate         | multiselect | no       | no          |                   |               |                     |
      | promotion_price | Promotion price | prices      | no       | no          |                   |               |                     |
    And the following "climate" attribute options: Hot and Cold
    And the following product values:
      | product   | attribute          | value                  | locale | scope  |
      | my-jacket | handmade           | yes                    |        |        |
      | my-jacket | made_in_france     | no                     |        |        |
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
    And the following product rules:
      | code             | priority |
      | copy_name_jacket | 10       |
    And the following product rule conditions:
      | rule             | field | operator | value     |
      | copy_name_jacket | sku   | =        | my-jacket |
    And the following product rule copier actions:
      | rule             | from_field         | to_field        | from_locale | to_locale | from_scope | to_scope |
      | copy_name_jacket | handmade           | made_in_france  |             |           |            |          |
      | copy_name_jacket | release_date       | release_date    |             |           | mobile     | tablet   |
      | copy_name_jacket | datasheet          | report          |             |           |            |          |
      | copy_name_jacket | side_view          | top_view        |             |           |            |          |
      | copy_name_jacket | length             | width           |             |           |            |          |
      | copy_name_jacket | weather_conditions | climate         |             |           |            |          |
      | copy_name_jacket | number_in_stock    | number_in_stock |             |           | mobile     | tablet   |
      | copy_name_jacket | main_color         | secondary_color |             |           |            |          |
      | copy_name_jacket | name               | name            | en_US       | fr_FR     |            |          |
      | copy_name_jacket | description        | description     | en_US       | fr_FR     | mobile     | tablet   |
    Given the product rule "copy_name_jacket" is executed
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

  Scenario: Successfully execute a rule with copier actions to update empty values on all kind of attributes
    Given the following products:
      | sku       | family  | weather_conditions |
      | my-jacket | jackets | dry                |
    And the following attributes:
      | code            | label           | type        | scopable | localizable | allowedExtensions | metric_family | default_metric_unit |
      | made_in_france  | Made in France  | boolean     | no       | no          |                   |               |                     |
      | report          | Report          | file        | no       | no          | txt               |               |                     |
      | climate         | Climate         | multiselect | no       | no          |                   |               |                     |
      | promotion_price | Promotion price | prices      | no       | no          |                   |               |                     |
    And the following "climate" attribute options: Hot and Cold
    And the following product values:
      | product   | attribute          | value                  | locale | scope  |
      | my-jacket | handmade           |                        |        |        |
      | my-jacket | made_in_france     | no                     |        |        |
      | my-jacket | release_date       |                        |        | mobile |
      | my-jacket | release_date       | 2015-09-18             |        | tablet |
      | my-jacket | datasheet          |                        |        |        |
      | my-jacket | report             | %fixtures%/akeneo.txt  |        |        |
      | my-jacket | side_view          |                        |        |        |
      | my-jacket | top_view           | %fixtures%/akeneo2.jpg |        |        |
      | my-jacket | length             |                        |        |        |
      | my-jacket | width              | 55 CENTIMETER          |        |        |
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
    And the following product rules:
      | code             | priority |
      | copy_name_jacket | 10       |
    And the following product rule conditions:
      | rule             | field | operator | value     |
      | copy_name_jacket | sku   | =        | my-jacket |
    And the following product rule copier actions:
      | rule             | from_field         | to_field        | from_locale | to_locale | from_scope | to_scope |
      | copy_name_jacket | handmade           | made_in_france  |             |           |            |          |
      | copy_name_jacket | release_date       | release_date    |             |           | mobile     | tablet   |
      | copy_name_jacket | datasheet          | report          |             |           |            |          |
      | copy_name_jacket | side_view          | top_view        |             |           |            |          |
      | copy_name_jacket | length             | width           |             |           |            |          |
      | copy_name_jacket | weather_conditions | climate         |             |           |            |          |
      | copy_name_jacket | number_in_stock    | number_in_stock |             |           | mobile     | tablet   |
      | copy_name_jacket | main_color         | secondary_color |             |           |            |          |
      | copy_name_jacket | name               | name            | en_US       | fr_FR     |            |          |
      | copy_name_jacket | description        | description     | en_US       | fr_FR     | mobile     | tablet   |
    Given the product rule "copy_name_jacket" is executed
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
