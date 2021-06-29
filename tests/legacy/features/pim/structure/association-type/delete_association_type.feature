@javascript
Feature: Delete an association type
  In order to manage association types in the catalog
  As a product manager
  I need to be able to delete association types

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the association types page
    Then the grid should contain 4 elements
    And I should see association type Pack

  Scenario: Successfully delete a association type from the edit page
    Given I edit the "Pack" association type
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then the grid should contain 3 elements
    And I should not see association type Pack
