@javascript
Feature: Filter proposals
  In order to easily find propositions for the product
  As an owner
  I need to be able to filter them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
      | white-boots | boots  |
    And the following propositions:
      | product     | status      | author | locale |
      | black-boots | in progress | admin  | fr_FR  |
      | black-boots | ready       | peter  | en_US  |
      | white-boots | ready       | julia  | en_US  |
    And I am logged in as "admin"

  Scenario: Successfully filter propositions
    Given I edit the "black-boots" product
    When I visit the "Propositions" tab
    Then the grid should contain 2 elements
    And I should see entities admin and peter
    And I should be able to use the following filters:
      | filter         | value                   | result |
      | Status         | In progress             | admin  |
      | Locale context | English (United States) | peter  |
