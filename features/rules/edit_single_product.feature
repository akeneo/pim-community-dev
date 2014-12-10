Feature: Edit a single product with rules appliance
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed when I edit and save a product

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
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator | value     |
      | rule1 | sku   | =        | my-loafer |
    And the following product rule setter actions:
      | rule  | field  | value     | locale |
      | rule1 | name   | My loafer | en_US  |
  Scenario: Successfully create, edit and save a product
    Given the product rule "rule1" is executed
    Then I am on the "my-loafer" product page
    And the product Name should be "My loafer"

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
      | code  | priority |
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator    | value |
      | rule1 | sku   | STARTS WITH | my    |
    And the following product rule setter actions:
      | rule  | field  | value     | locale |
      | rule1 | name   | My loafer | en_US  |
  Scenario: Successfully create, edit and save a product
    Given the product rule "rule1" is executed
    Then I am on the "my-loafer" product page
    And the product Name should be "My loafer"

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
      | code  | priority |
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator  | value |
      | rule1 | sku   | ENDS WITH | fer   |
    And the following product rule setter actions:
      | rule  | field  | value     | locale |
      | rule1 | name   | My loafer | en_US  |
  Scenario: Successfully create, edit and save a product
    Given the product rule "rule1" is executed
    Then I am on the "my-loafer" product page
    And the product Name should be "My loafer"

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
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator | value |
      | rule1 | sku   | CONTAINS | lo    |
    And the following product rule setter actions:
      | rule  | field  | value     | locale |
      | rule1 | name   | My loafer | en_US  |
  Scenario: Successfully create, edit and save a product
    Given the product rule "rule1" is executed
    Then I am on the "my-loafer" product page
    And the product Name should be "My loafer"

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
      | code  | priority |
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator         | value |
      | rule1 | sku   | DOES NOT CONTAIN | not   |
    And the following product rule setter actions:
      | rule  | field  | value     | locale |
      | rule1 | name   | My loafer | en_US  |
  Scenario: Successfully create, edit and save a product
    Given the product rule "rule1" is executed
    Then I am on the "my-loafer" product page
    And the product Name should be "My loafer"

  Scenario: Successfully execute a rule with in condition
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
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator | value     |
      | rule1 | sku   | IN       | my-loafer |
    And the following product rule setter actions:
      | rule  | field  | value     | locale |
      | rule1 | name   | My loafer | en_US  |
  Scenario: Successfully create, edit and save a product
    Given the product rule "rule1" is executed
    Then I am on the "my-loafer" product page
    And the product Name should be "My loafer"

  Scenario: Successfully execute a rule with in condition
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
      | code  | priority |
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator | value     |
      | rule1 | sku   | =        | my-loafer |
    And the following product rule setter actions:
      | rule  | field | value  |
      | rule1 | size  | 42     |
      | rule1 | color | red    |
      | rule1 | price | 12,EUR |
    Given I am on the "my-loafer" product page
    And the product Size should be "35"
    And the product Color should be "Blue"
    When I press the "Save" button
    Then the product Size should be "42"
    And the product Color should be "Red"

  Scenario: Successfully execute a rule with in condition
    Given the following products:
      | sku       | family  | name-fr_FR | weather_conditions |
      | my-loafer | sandals | boot       | dry                |
    And the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-loafer | name        | White loafer           | en_US  |        |
      | my-loafer | name        | Mocassin blanc         | fr_FR  |        |
      | my-loafer | description | A stylish white loafer | en_US  | mobile |
    And the following product rules:
      | code  | priority |
      | rule1 | 10       |
    And the following product rule conditions:
      | rule  | field | operator | value     |
      | rule1 | sku   | =        | my-loafer |
    And the following product rule copier actions:
      | rule  | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | rule1 | description | description | en_US       | en_US     | mobile     | tablet   |
    Given I am on the "my-loafer" product page
    And I should see "A stylish white loafer"
    When I press the "Save" button
    And I should see "A stylish white loafer"
