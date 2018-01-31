@javascript
Feature: Remove a product model
  In order to delete an unnecessary product model from my PIM
  As a product manager
  I need to be able to remove a product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  @ce
  Scenario: Successfully delete a product model from the grid
    Given I am on the products grid
    When I click on the "Delete the product" action of the row which contains "amor"
    Then I should see the text "Confirm deletion"
    When I confirm the removal
    Then I should not see product 1111111111
    And  I should not see product 1111111112

  Scenario: Successfully delete a product model from the edit form
    Given I am on the "amor" product model page
    And  I press the secondary action "Delete"
    Then I should see the text "Confirm deletion"
    When I confirm the removal
    Then I should not see product 1111111111
    And  I should not see product 1111111112
