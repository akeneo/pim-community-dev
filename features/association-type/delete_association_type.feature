@javascript
Feature: Delete an association type
  In order to manage association types in the catalog
  As a product manager
  I need to be able to delete association types

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully delete an association type from the grid
    Given I am on the association types page
    Then I should see association type X_SELL
    When I click on the "Delete" action of the row which contains "X_SELL"
    And I confirm the deletion
    Then I should not see association type X_SELL

  Scenario: Successfully delete a association type from the edit page
    Given I edit the "UPSELL" association type
    When I press the "Delete" button
    And I confirm the deletion
    Then the grid should contain 3 elements
    And I should not see association type UPSELL
