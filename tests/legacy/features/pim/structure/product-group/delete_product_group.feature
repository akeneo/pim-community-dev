@javascript
Feature: Delete a product group
  In order to manager product groups for the catalog
  As a product manager
  I need to be able to delete groups

  Background:
    Given the "default" catalog configuration
    And the following product groups:
      | code | label-en_US | type   |
      | MUG  | MUG Akeneo  | X_SELL |
    And I am logged in as "Julia"

  Scenario: Successfully delete a product group
    Given I edit the "MUG" product group
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should not see group "MUG"
