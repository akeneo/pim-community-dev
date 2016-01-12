@javascript
Feature: Delete a product group
  In order to manager product groups for the catalog
  As a product manager
  I need to be able to delete groups

  Background:
    Given the "default" catalog configuration
    And the following product groups:
      | code | label      | type   |
      | MUG  | MUG Akeneo | X_SELL |
    And I am logged in as "Julia"

  Scenario: Successfully delete a product group from the grid
    Given I am on the product groups page
    And I should see groups MUG
    When I click on the "Delete" action of the row which contains "MUG"
    And I confirm the deletion
    Then I should not see product group MUG

  Scenario: Successfully delete a product group
    Given I edit the "MUG" product group
    When I press the "Delete" button
    And I confirm the deletion
    Then I should not see group "MUG"
