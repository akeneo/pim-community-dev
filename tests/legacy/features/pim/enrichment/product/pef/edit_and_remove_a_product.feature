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
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | Family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I visit the "Sizes" group
    And I fill in the following information:
      | Size | 36 |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."

  Scenario: Successfully delete a product from the edit form
    Given I press the secondary action "Delete"
    Then I should see the text "Confirm deletion"
    When I confirm the removal
    Then I should not see product boots
