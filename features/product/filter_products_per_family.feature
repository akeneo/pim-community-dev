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
    When I filter by "Family" with value "Computers"
    Then I should see products PC and Laptop
    And I should not see products Amplifier, CD changer, Whirlpool and Electrolux
    When I filter by "Family" with value "Hi-fi"
    Then I should see products Amplifier and CD changer
    And I should not see products PC, Laptop, Whirlpool and Electrolux
    When I filter by "Family" with value "Washing machines"
    Then I should see products Whirlpool and Electrolux
    And I should not see products Amplifier, CD changer, PC and Laptop
