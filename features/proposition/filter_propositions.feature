@javascript
Feature: Filter proposals
  In order to easily find propositions for the product
  As a product manager
  I need to be able to filter them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
      | white-boots | boots  |
    And the following propositions:
      | product     | status      | author | locale |
      | black-boots | in progress | Sandra | fr_FR  |
      | black-boots | ready       | Mary   | en_US  |
      | white-boots | ready       | Sandra | en_US  |
    And I am logged in as "Julia"

  Scenario: Successfully filter propositions
    Given I edit the "black-boots" product
    When I visit the "Propositions" tab
    Then the grid should contain 2 elements
    And I should see entities Sandra and Mary
    And I should be able to use the following filters:
      | filter         | value       | result |
      | Status         | In progress | Sandra |
      | Status         | Ready       | Mary   |
