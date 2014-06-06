@javascript
Feature: Sort proposals
  In order to easily manage proposals for a specific product
  As an admin
  I need to be able to sort proposals by several columns

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
      | white-boots | boots  |
    And the following proposals:
      | product     | status   | author | locale |
      | black-boots | approved | admin  | en_US  |
      | black-boots | waiting  | peter  | fr_FR  |
      | grey-boots  | canceled | julia  | en_US  |
    And I am logged in as "admin"

  Scenario: Successfully sort proposals in the grid
    Given I edit the "black-boots" product
    When I visit the "Proposals" tab
    Then the grid should contain 2 elements
    And the rows should be sorted descending by proposed at
    And I should be able to sort the rows by author, proposed at and status