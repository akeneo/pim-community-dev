@javascript
Feature: Filter products per family
  In order to enrich my catalog
  As a user
  I need to be able to manually filter products per family

  Background:
    Given the following families:
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
    When I filter per family Computers
    Then I should see products PC and Laptop
    And I should not see products Amplifier, CD changer, Whirlpool and Electrolux
    When I filter per family Hi-fi
    Then I should see products Amplifier and CD changer
    And I should not see products PC, Laptop, Whirlpool and Electrolux
    When I filter per family Washing machines
    Then I should see products Whirlpool and Electrolux
    And I should not see products Amplifier, CD changer, PC and Laptop

  Scenario: Successfully filter products by multiple families
    Given I am on the products page
    Then I should see the filter "Family"
    When I filter per families Hi-fi and Computers
    And I should see products Amplifier, CD changer, PC and Laptop
    Then I should not see products Whirlpool and Electrolux
