Feature: Update multiple product by applying rules
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed and correctly applied to products

  Background:
    Given a "footwear" catalog configuration

  @integration-back
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
    When the product rule "set_name" is executed
    Then the en_US unscoped name of "a-my-loafer" should be "new name"
    And the fr_FR unscoped name of "a-my-loafer" should be "Mocassin blanc"
    And the en_US unscoped name of "a-boot" should be "new name"
    And the en_US unscoped name of "a-fork" should be "new name"
    And the en_US unscoped name of "a-rangers" should be "new name"
    And the en_US unscoped name of "mug" should be "Mug"

  @integration-back
  Scenario: Successfully execute a rule with a copy action on multiple products
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
    When the product rule "copy_name" is executed
    Then the fr_FR unscoped name of "my-loafer" should be "Mocassin blanc"
    And the fr_FR unscoped name of "boot" should be "Boots"
    And the fr_FR unscoped name of "rangers" should be "Rangers"

  @integration-back
  Scenario: Successfully execute a rule with a copy action and a NOT EMPTY condition on multiple products
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
    When the product rule "copy_name" is executed
    Then the fr_FR unscoped name of "my-loafer" should be "White loafer"
    And the fr_FR unscoped name of "fork" should be ""
    And the fr_FR unscoped name of "rangers" should be "Rangers"
