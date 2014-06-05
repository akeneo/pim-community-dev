@javascript
Feature: Remove a product
  In order to delete an unnecessary product from my PIM
  As a product manager
  I need to be able to remove a product

  Background:
    Given the "default" catalog configuration
    And a "CD player" product
    And I am logged in as "Julia"

  Scenario: Successfully delete a product from the grid
    Given I am on the products page
    Then I should see products CD player
    When I click on the "Delete the product" action of the row which contains "CD player"
    Then I should see "Delete confirmation"
    And I confirm the removal
    Then I should not see product CD player

  Scenario: Successfully delete a product from the edit form
    Given I am on the "CD player" product page
    And I press the "Delete" button
    Then I should see "Delete confirmation"
    And I confirm the removal
    Then I should not see product CD player
