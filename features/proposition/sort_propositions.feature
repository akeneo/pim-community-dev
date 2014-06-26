@javascript @skip
Feature: Sort proposals
  In order to easily manage propositions for a specific product
  As an admin
  I need to be able to sort propositions by several columns

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

  Scenario: Successfully sort propositions in the grid
    Given I edit the "black-boots" product
    When I visit the "Propositions" tab
    Then the grid should contain 2 elements
    And the rows should be sorted descending by proposed at
    And I should be able to sort the rows by author, proposed at and status
