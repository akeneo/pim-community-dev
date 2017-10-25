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

  Scenario: Successfully delete a family from the edit page
    Given I am on the "sneakers" family page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be on the families page
    And the grid should contain 4 elements
    But I should not see family Sneakers

  @jira https://akeneo.atlassian.net/browse/PIM-6031
  Scenario: Successfully delete a family used by a product
    Given the following product:
      | sku | family   |
      | foo | sneakers |
    When I am on the products grid
    And I display the columns SKU, Family
    Then I should see the text "Sneakers"
    When I am on the "sneakers" family page
    And I press the secondary action "Delete"
    And I confirm the deletion
    And I am on the products grid
    And I display the columns SKU, Family
    Then I should not see the text "Sneakers"
    When I edit the "foo" product
    Then I should see the text "Family None"
