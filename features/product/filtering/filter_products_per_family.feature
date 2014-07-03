@javascript
Feature: Filter products per family
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products per family

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

  Scenario: Successfully filter products by a single family
    Given I am on the products page
    And the grid should contain 7 elements
    Then I should see the filter "Family"
    And I should be able to use the following filters:
      | filter | value                | result                               |
      | Family | [computers]          | PC and Laptop                        |
      | Family | [hi_fi],[computers]  | Amplifier, CD changer, PC and Laptop |
      | Family | [washing_machines]   | Whirlpool and Electrolux             |
      | Family | is empty             | Mug                                  |
      | Family | [computers],is empty | PC, Laptop and Mug                   |
