@javascript
Feature: Delete an association
  In order to manage associations in the catalog
  As a user
  I need to be able to delete associations

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully delete an association from the grid
    Given I am on the associations page
    Then I should see association X_SELL
    When I click on the "Delete" action of the row which contains "X_SELL"
    And I confirm the deletion
    Then I should not see association X_SELL

  Scenario: Successfully delete a association from the edit page
    Given I edit the "UPSELL" association
    When I press the "Delete" button
    And I confirm the deletion
    Then the grid should contain 3 elements
    And I should not see association UPSELL
