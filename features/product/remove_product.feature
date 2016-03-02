@javascript
Feature: Remove a product
  In order to delete an unnecessary product from my PIM
  As a product manager
  I need to be able to remove a product

  Background:
    Given the "footwear" catalog configuration
    And the following family:
      | code       | attributes                                                       |
      | high_heels | sku, name, description, price, rating, size, color, manufacturer |
    And the following attributes:
      | code        | label       | type   | metric family | default metric unit | families                 |
      | weight      | Weight      | metric | Weight        | GRAM                | boots, sneakers, sandals |
      | heel_height | Heel Height | metric | Length        | CENTIMETER          | high_heels               |
    And the following products:
      | sku       | family     |
      | boots     | high_heels |
    And I am logged in as "Julia"

  Scenario: Successfully delete a product from the grid
    Given I am on the products page
    Then I should see product boots
    When I click on the "Delete the product" action of the row which contains "boots"
    Then I should see "Delete confirmation"
    When I confirm the removal
    Then I should not see product boots

  Scenario: Successfully delete a product from the edit form
    Given I am on the "boots" product page
    And I press the "Delete" button
    Then I should see "Confirm deletion"
    When I confirm the removal
    Then I should not see product boots
