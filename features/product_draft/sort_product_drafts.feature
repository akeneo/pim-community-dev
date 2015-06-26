@javascript
Feature: Sort product drafts
  In order to easily manage product drafts for a specific product
  As a product manager
  I need to be able to sort product drafts by several columns

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
      | white-boots | boots  |
    And the following product drafts:
      | product     | status      | author |
      | black-boots | in progress | Julia  |
      | black-boots | ready       | Sandra |
      | white-boots | ready       | Mary   |
    And I am logged in as "Julia"

  @skip-pef
  Scenario: Successfully sort product drafts in the grid
    Given I edit the "black-boots" product
    When I visit the "Proposals" tab
    Then the grid should contain 2 elements
    And the rows should be sorted descending by proposed at
    And I should be able to sort the rows by author, proposed at and status
