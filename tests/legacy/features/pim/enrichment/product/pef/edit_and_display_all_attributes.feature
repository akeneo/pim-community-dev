@javascript
Feature: Edit and see all attributes
  In order to enrich a product from my PIM
  As a product manager
  I need to be able to work with a product and see all attributes

  Background:
    Given the "footwear" catalog configuration
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

  Scenario: Successfully edit the product and check that all attributes are visible
    Then I should not see the text "Media"
    Then I should not see the text "Colors"
    Then I should not see the text "Marketing"
    And I visit the "All" group
    Then I should see the text "3 missing required attributes"
    Then I should see the text "Media"
    Then I should see the text "Colors"
    Then I should see the text "Marketing"
    Then I should see the text "Sku"
    Then I should see the text "Name"
    Then I should see the text "Description"
