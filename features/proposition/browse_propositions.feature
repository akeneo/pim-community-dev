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
      | product     | author | locale |
      | black-boots | admin  | fr_FR  |
      | black-boots | peter  | en_US  |
      | white-boots | julia  | en_US  |
    And I am logged in as "admin"

  Scenario: Successfully display propositions
    Given I edit the "black-boots" product
    When I visit the "Propositions" tab
    Then the grid should contain 2 elements
    And I should see the columns Author, Locale context, Changes and Proposed at
    And I should see entities admin and peter
