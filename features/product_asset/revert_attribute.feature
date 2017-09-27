@javascript
Feature: Revert an assets collection in a product
  In order to revert a collection of assets
  As a product manager

  Background:
    Given the "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert an assets collection in a product
    Given I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | jeans   |
      | family | Jackets |
    And I press the "Save" button in the popin
    And I wait to be on the "jeans" product page
    And I visit the "Media" group
    And I start to manage assets for "gallery"
    And I check the row "paint"
    And I check the row "chicagoskyline"
    Then the asset basket should contain chicagoskyline, paint
    And I confirm the asset modification
    Then the "gallery" asset gallery should contain paint, chicagoskyline
    When I save the product
    And I visit the "History" column tab
    Then I should see history:
      | version | property | value                |
      | 2       | gallery  | chicagoskyline,paint |
    And I visit the "Attributes" column tab
    And I start to manage assets for "gallery"
    And I search "paint"
    And I uncheck the row "paint"
    Then the asset basket should contain chicagoskyline
    And I confirm the asset modification
    Then the "gallery" asset gallery should contain chicagoskyline
    When I save the product
    And I visit the "History" column tab
    Then I should see history:
      | version | property | value   |
      | 3       | gallery  | chicagoskyline |
    When I revert the product version number 2
    Then the product "jeans" should have the following values:
      | gallery | chicagoskyline, paint |
