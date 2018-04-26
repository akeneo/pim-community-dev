@javascript
Feature: Delete a family
  In order to manage families in the catalog
  As an administrator
  I need to be able to delete families

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully delete a family from the grid
    Given I am on the families grid
    Then I should see family Boots
    When I click on the "Delete" action of the row which contains "Boots"
    And I confirm the deletion
    Then I should be on the families page
    But I should not see family Boots

  Scenario: Failed to delete a family with family variants from the grid
    Given the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1 |
      | family_variant | boots  | color          | description          |
    And I am on the families grid
    When I click on the "Delete" action of the row which contains "Boots"
    And I confirm the deletion
    Then I should see a dialog with the following content:
      | title        | content                                                                |
      | Delete Error | Can not remove family "boots" because it is linked to family variants. |
    When I press the "OK" button
    Then I should be on the families page
    And I should see family Boots

  Scenario: Successfully delete a family from the edit page
    Given I am on the "sneakers" family page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be on the families page
    And the grid should contain 4 elements
    But I should not see family Sneakers

  Scenario: Fail to delete a family used by a product
    Given the following product:
      | sku | family   |
      | foo | sneakers |
    And I am on the families grid
    When I click on the "Delete" action of the row which contains "Sneakers"
    And I confirm the deletion
    Then I should see the text "Delete error"
    And there should be a "Sneakers" family
