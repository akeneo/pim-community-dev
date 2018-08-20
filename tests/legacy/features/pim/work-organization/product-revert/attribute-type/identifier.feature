@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a identifier attribute
    Given I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | jeans |
      | Family | Pants |
    And I press the "Save" button in the popin
    And I wait to be on the "jeans" product page
    When I change the "SKU" to "pantalon"
    And I save the product
    And I should not see the text "There are unsaved changes."
    And the history of the product "pantalon" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "jeans" should have the following values:
    | sku | jeans |
