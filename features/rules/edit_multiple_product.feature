Feature: Read multiple products with applied rules
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed and correctly applied to products

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"

  Scenario: Successfully execute a rule with a setter action on multiple products
    Given the following products:
    | sku         | family  |
    | a-my-loafer | sandals |
    | a-boot      | sandals |
    | mug         |         |
    | a-fork      |         |
    | a-rangers   | sandals |
    And the following product values:
    | product     | attribute | value                  | locale | scope  |
    | a-my-loafer | name      | White loafer           | en_US  |        |
    | a-my-loafer | name      | Mocassin blanc         | fr_FR  |        |
    | a-my-loafer | name      | A stylish white loafer | en_US  | mobile |
    | a-boot      | name      | Boots                  | en_US  | mobile |
    | mug         | name      | Mug                    | en_US  | mobile |
    | fork        | name      | Fork                   | en_US  | mobile |
    | a-rangers   | name      | Rangers                | en_US  | mobile |
    And the following product rules:
    | code     | priority |
    | set_name | 10       |
    And the following product rule conditions:
    | rule     | field | operator    | value |
    | set_name | sku   | STARTS WITH | a-    |
    And the following product rule setter actions:
    | rule     | field | value    | locale |
    | set_name | name  | new name | en_US  |
    Given the product rule "set_name" is executed
    And I am on the "a-my-loafer" product page
    Then the product Name should be "new name"
    When I am on the "a-boot" product page
    Then the product Name should be "new name"
    When I am on the "a-fork" product page
    Then the product Name should be "new name"
    When I am on the "a-rangers" product page
    Then the product Name should be "new name"
    When I am on the "mug" product page
    Then the product Name should be "Mug"

  @javascript
  Scenario: Successfully execute a rule with a setter action on multiple products
    Given the following products:
    | sku       | family  |
    | my-loafer | sandals |
    | boot      | sandals |
    | mug       |         |
    | fork      |         |
    | rangers   | sandals |
    And the following product values:
    | product   | attribute | value          | locale | scope  |
    | my-loafer | name      | White loafer   | en_US  |        |
    | my-loafer | name      | Mocassin blanc | fr_FR  |        |
    | boot      | name      | Boots          | en_US  | mobile |
    | mug       | name      | Mug            | en_US  | mobile |
    | mug       | name      |                | fr_FR  | mobile |
    | fork      | name      | Fork           | en_US  | mobile |
    | fork      | name      |                | frFR   | mobile |
    | rangers   | name      | Rangers        | en_US  | mobile |
    | rangers   | name      |                | fr_FR  | mobile |
    And the following product rules:
    | code      | priority |
    | copy_name | 10       |
    And the following product rule conditions:
    | rule      | field | operator | value | locale |
    | copy_name | name  | EMPTY    |       | fr_FR  |
    And the following product rule copier actions:
      | rule      | from_field | to_field | from_locale | to_locale | from_scope | to_scope |
      | copy_name | name       | name     | en_US       | fr_FR     |            |          |
    Given the product rule "copy_name" is executed
    And I am on the "my-loafer" product page
    When I switch the locale to "French (France)"
    Then the product name should be "Mocassin blanc"
    When I am on the "boot" product page
    And I switch the locale to "French (France)"
    Then the product name should be "Boots"
    When I am on the "fork" product page
    And I switch the locale to "French (France)"
    Then the product name should be "Fork"
    When I am on the "rangers" product page
    And I switch the locale to "French (France)"
    Then the product name should be "Rangers"
    When I am on the "mug" product page
    And I switch the locale to "French (France)"
    Then the product name should be "Mug"
