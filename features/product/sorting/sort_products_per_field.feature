@javascript
Feature: Sort products per fields
  In order to enrich my catalog
  As a regular user
  I need to be able to manually sort products per fields

  Background:
    Given the "default" catalog configuration
    And the following families:
      | code             |
      | computers        |
      | hi_fi            |
      | washing_machines |
    And the following products:
      | sku        | family           |
      | PC         | computers        |
      | Laptop     | computers        |
      | Amplifier  | hi_fi            |
      | CD changer | hi_fi            |
      | Whirlpool  | washing_machines |
      | Electrolux | washing_machines |
      | Mug        |                  |
    And I am logged in as "Mary"

  Scenario: Successfully sort products by family
    Given I am on the products page
    And the grid should contain 7 elements
    And I should be able to sort the rows by family

  Scenario: Successfully sort products by status
    Given I am on the products page
    And the grid should contain 7 elements
    And I should be able to sort the rows by status

  # TODO : calculate completeness
  Scenario: Successfully sort products by complete
    Given I am on the products page
    And the grid should contain 7 elements
    And I should be able to sort the rows by status

  Scenario: Successfully sort products by created at
    Given I am on the products page
    And the grid should contain 7 elements
    And I should be able to sort the rows by created at

  Scenario: Successfully sort products by updated at
    Given I am on the products page
    And the grid should contain 7 elements
    And I should be able to sort the rows by updated at

  Scenario: Successfully sort products by status
    Given I am on the "PC" product page
    When I enable the product
    And I press the "Save" button
    And I am on the "Laptop" product page
    And I disable the product
    And I press the "Save" button
    And I am on the "Amplifier" product page
    And I disable the product
    And I press the "Save" button
    And I am on the "CD changer" product page
    And I enable the product
    And I press the "Save" button
    And I am on the "Whirlpool" product page
    And I enable the product
    And I press the "Save" button
    And I am on the "Electrolux" product page
    And I disable the product
    And I press the "Save" button
    And I am on the products page
    And I am on the "Mug" product page
    And I enable the product
    And I press the "Save" button
    When I am on the products page
    And the grid should contain 7 elements
    And I should be able to sort the rows by status
