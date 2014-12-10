Feature: Read a single product with applied rules
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed and correctly applied to the product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"

  Scenario: Successfully execute a rule with equals condition
    Given the following products:
      | sku       | family  |
      | my-loafer | sandals |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code  | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator | value     |
      | set_name | sku   | =        | my-loafer |
    And the following product rule setter actions:
      | rule     | field  | value     | locale |
      | set_name | name   | My loafer | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-loafer" product page
    Then the product Name should be "My loafer"

  Scenario: Successfully execute a rule with starts with condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-loafer | sandals | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator    | value |
      | set_name | sku   | STARTS WITH | my    |
    And the following product rule setter actions:
      | rule     | field  | value     | locale |
      | set_name | name   | My loafer | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-loafer" product page
    Then the product Name should be "My loafer"

  Scenario: Successfully execute a rule with ends with condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-loafer | sandals | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator  | value |
      | set_name | sku   | ENDS WITH | fer   |
    And the following product rule setter actions:
      | rule     | field  | value     | locale |
      | set_name | name   | My loafer | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-loafer" product page
    Then the product Name should be "My loafer"

  Scenario: Successfully execute a rule with contains condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-loafer | sandals | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code  | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator | value |
      | set_name | sku   | CONTAINS | lo    |
    And the following product rule setter actions:
      | rule     | field  | value     | locale |
      | set_name | name   | My loafer | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-loafer" product page
    Then the product Name should be "My loafer"

  Scenario: Successfully execute a rule with does not contain condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-loafer | sandals | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator         | value |
      | set_name | sku   | DOES NOT CONTAIN | not   |
    And the following product rule setter actions:
      | rule     | field  | value     | locale |
      | set_name | name   | My loafer | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-loafer" product page
    Then the product Name should be "My loafer"

  Scenario: Successfully execute a rule with IN condition
    Given the following products:
      | sku       | family  | name-fr_FR |
      | my-loafer | sandals | boot       |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code     | priority |
      | set_name | 10       |
    And the following product rule conditions:
      | rule     | field | operator | value     |
      | set_name | sku   | IN       | my-loafer |
    And the following product rule setter actions:
      | rule     | field  | value     | locale |
      | set_name | name   | My loafer | en_US  |
    Given the product rule "set_name" is executed
    When I am on the "my-loafer" product page
    Then the product Name should be "My loafer"

  @javascript
  Scenario: Successfully execute a rule with a setter action
    Given the following products:
      | sku       | family  | name-fr_FR | weather_conditions |
      | my-loafer | sandals | boot       | dry                |
    And the following product values:
      | product   | attribute | value          | locale | scope  |
      | my-loafer | name      | White loafer   | en_US  |        |
      | my-loafer | name      | Mocassin blanc | fr_FR  |        |
      | my-loafer | size      | 35             | en_US  | mobile |
      | my-loafer | color     | blue           | en_US  | mobile |
    And the following product rules:
      | code           | priority |
      | rule_sku_loafer | 10       |
    And the following product rule conditions:
      | rule            | field | operator | value     |
      | rule_sku_loafer | sku   | =        | my-loafer |
    And the following product rule setter actions:
      | rule            | field | value  |
      | rule_sku_loafer | size  | 42     |
      | rule_sku_loafer | color | red    |
      | rule_sku_loafer | price | 12,EUR |
    Given the product rule "rule_sku_loafer" is executed
    Then I am on the "my-loafer" product page
    And the product Name should be "White loafer"
    Then the product Size should be "42"
    And the product Color should be "Red"

  @javascript
  Scenario: Successfully execute a rule with a copier action
    Given the following products:
      | sku       | family  | weather_conditions |
      | my-loafer | sandals | dry                |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code             | priority |
      | copy_name_loafer | 10       |
    And the following product rule conditions:
      | rule             | field | operator | value     |
      | copy_name_loafer | sku   | =        | my-loafer |
    And the following product rule copier actions:
      | rule             | from_field | to_field | from_locale | to_locale | from_scope | to_scope |
      | copy_name_loafer | name       | name     | en_US       | fr_FR     |            |          |
    Given the product rule "copy_name_loafer" is executed
    When I am on the "my-loafer" product page
    And I switch the locale to "French (France)"
    Then the product name should be "White loafer"
