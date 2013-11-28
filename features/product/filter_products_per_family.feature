@javascript
Feature: Filter products per family
  In order to enrich my catalog
  As a user
  I need to be able to manually filter products per family

  Background:
    Given the "default" catalog configuration
    And the following families:
      | code             |
      | Computers        |
      | Hi-fi            |
      | Washing machines |
    And the following products:
      | sku        | family           |
      | PC         | Computers        |
      | Laptop     | Computers        |
      | Amplifier  | Hi-fi            |
      | CD changer | Hi-fi            |
      | Whirlpool  | Washing machines |
      | Electrolux | Washing machines |
    And I am logged in as "admin"

  Scenario: Successfully filter products by a single family
    Given I am on the products page
    Then I should see the filter "Family"
    And I should be able to use the following filters:
      | filter | value            | result                   |
      | Family | Computers        | PC and Laptop            |
      | Family | Hi-fi            | Amplifier and CD changer |
      | Family | Washing machines | Whirlpool and Electrolux |
