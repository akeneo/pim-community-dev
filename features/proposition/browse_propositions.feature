@javascript
Feature: Browse propositions for a specific product
  In order to list the existing propositions for a specific product
  As an owner
  I need to be able to see propositions

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

  Scenario: Successfully display propositions
    Given I edit the "black-boots" product
    When I visit the "Propositions" tab
    Then the grid should contain 2 elements
    And I should see the columns Author, Locale context, Changes, Proposed at and Status
    And I should see entities admin and peter
