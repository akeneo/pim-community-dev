@javascript
Feature: Edit and remove a product
  In order to delete an unnecessary product from my PIM
  As a product manager
  I need to be able to work with a product and then remove it

  Background:
    Given the "footwear" catalog configuration
    And the following family:
      | code  | attributes                                                       |
      | shoes | sku,name,description,price,rating,size,color,manufacturer,length |
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU             | boots |
      | Choose a family | shoes |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I fill in the following information:
      | Length | 5.0000 Centimeter |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."

  Scenario: Successfully edit and then delete a product from the grid
    Given I am on the products page
    Then I should see product boots
    When I click on the "Delete the product" action of the row which contains "boots"
    Then I should see "Delete confirmation"
    When I confirm the removal
    Then I should be on the products page
    And I should not see product boots

  Scenario: Successfully delete a product from the edit form
    Given I press the secondary action "Delete"
    Then I should see "Confirm deletion"
    When I confirm the removal
    Then I should not see product boots
