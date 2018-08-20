@javascript
Feature: Update multiple product by applying rules
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed and correctly applied to products

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "tablet" channel
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
    | product     | attribute | value          | locale |
    | a-my-loafer | name      | White loafer   | en_US  |
    | a-my-loafer | name      | Mocassin blanc | fr_FR  |
    | a-boot      | name      | Boots          | en_US  |
    | mug         | name      | Mug            | en_US  |
    | fork        | name      | Fork           | en_US  |
    | a-rangers   | name      | Rangers        | en_US  |
    And the following product rule definitions:
      """
      set_name:
        priority: 10
        conditions:
          - field:    sku
            operator: STARTS WITH
            value:    a-
        actions:
          - type:  set
            field: name
            value: new name
            locale: en_US
      """
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

  Scenario: Successfully execute a rule with a setter action on multiple products
    Given the following products:
    | sku       | family  |
    | my-loafer | sandals |
    | boot      | sandals |
    | mug       |         |
    | fork      |         |
    | rangers   | sandals |
    And the following product values:
    | product   | attribute | value          | locale |
    | my-loafer | name      | White loafer   | en_US  |
    | my-loafer | name      | Mocassin blanc | fr_FR  |
    | boot      | name      | Boots          | en_US  |
    | mug       | name      | Mug            | en_US  |
    | mug       | name      |                | fr_FR  |
    | fork      | name      | Fork           | en_US  |
    | fork      | name      |                | fr_FR  |
    | rangers   | name      | Rangers        | en_US  |
    | rangers   | name      |                | fr_FR  |
    And the following product rule definitions:
      """
      copy_name:
        priority: 10
        conditions:
          - field:    name
            operator: EMPTY
            value:    ~
            locale:   fr_FR
        actions:
          - type:        copy
            from_field:  name
            to_field:    name
            from_locale: en_US
            to_locale:   fr_FR
      """
    Given the product rule "copy_name" is executed
    And I am on the "my-loafer" product page
    When I switch the locale to "fr_FR"
    Then the product [name] should be "Mocassin blanc"
    When I am on the "boot" product page
    And I switch the locale to "fr_FR"
    Then the product [name] should be "Boots"
    When I am on the "fork" product page
    And I switch the locale to "fr_FR"
    Then the product [name] should be "Fork"
    When I am on the "rangers" product page
    And I switch the locale to "fr_FR"
    Then the product [name] should be "Rangers"
    When I am on the "mug" product page
    And I switch the locale to "fr_FR"
    Then the product [name] should be "Mug"

  Scenario: Successfully execute a rule with a setter action and a NOT EMPTY condition on multiple products
    Given the following products:
      | sku       | family  |
      | my-loafer | sandals |
      | fork      |         |
      | rangers   | sandals |
    And the following product values:
      | product   | attribute | value        | locale |
      | my-loafer | name      | White loafer | en_US  |
      | my-loafer | name      |              | fr_FR  |
      | fork      | name      | Fork         | en_US  |
      | fork      | name      |              | fr_FR  |
      | rangers   | name      | Rangers      | en_US  |
      | rangers   | name      |              | fr_FR  |
    And the following product rule definitions:
      """
      copy_name:
        priority: 10
        conditions:
          - field:    family
            operator: NOT EMPTY
            value:    ~
        actions:
          - type:        copy
            from_field:  name
            to_field:    name
            from_locale: en_US
            to_locale:   fr_FR
      """
    Given the product rule "copy_name" is executed
    And I am on the "my-loafer" product page
    When I switch the locale to "fr_FR"
    Then the product [name] should be "White loafer"
    When I am on the "fork" product page
    And I switch the locale to "fr_FR"
    Then the product [name] should be ""
    When I am on the "rangers" product page
    And I switch the locale to "fr_FR"
    Then the product [name] should be "Rangers"
