@javascript
Feature: Delete a family
  In order to manage families in the catalog
  As an administrator
  I need to be able to delete families

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully delete a family from the grid
    Given I am on the families page
    Then I should see family boots
    When I click on the "Delete" action of the row which contains "boots"
    And I confirm the deletion
    Then I should not see family boots

  Scenario: Successfully delete a family from the edit page
    Given I edit the "sneakers" family
    When I press the "Delete" button
    And I confirm the deletion
    Then the grid should contain 2 elements
    And I should not see family sneakers

  Scenario: Successfully delete a family used by a product
    Given the following product:
      | sku | family   |
      | foo | sneakers |
    And I edit the "sneakers" family
    When I press the "Delete" button
    And I confirm the deletion
    When I edit the "foo" product
    Then I should see "Family: N/A"
    When I save the product
    And I visit the "History" tab
    Then I should see history:
      | version | property | value |
      | 2       | family   |       |
