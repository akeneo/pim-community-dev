@javascript
Feature: Delete a variant group
  In order to manager variant groups for the catalog
  As a user
  I need to be able to delete variants

  Background:
    Given there is no variant
    And the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_multiselect  |
      | size      | Size       | pim_catalog_simpleselect |
    And the following variants:
      | code    | label          | attributes    |
      | TSHIRT  | T-Shirt Akeneo | size, color   |
      | MUG     | Mug Akeneo     | color         |
    And I am logged in as "admin"

  Scenario: Successfully delete a variant from the grid
    Given I am on the variants page
    And I should see variant MUG
    When I click on the "Delete" action of the row which contains "MUG"
    And I confirm the deletion
    Then I should see "Item was deleted"
    And I should not see variant MUG

  Scenario: Successfully delete a variant
    Given I edit the "TSHIRT" variant
    When I press the "Delete" button
    And I confirm the deletion
    Then I should see "Variant group successfully removed"
    And the grid should contain 1 element
    And I should not see variant "TSHIRT"
