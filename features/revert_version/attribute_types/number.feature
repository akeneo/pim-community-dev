@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a product number and leave it empty
    Given I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | jeans   |
      | family | Jackets |
    And I press the "Save" button in the popin
    And I wait to be on the "jeans" product page
    And I visit the "Marketing" group
    And I switch the scope to "tablet"
    And I change the "Number in stock" to "100"
    And I save the product
    And I should not see the text "There are unsaved changes."
    And the history of the product "jeans" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "jeans" should have the following values:
    | number_in_stock-tablet |  |

  Scenario: Successfully revert a number attribute
    Given I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | t-shirt |
      | family | Jackets |
    And I press the "Save" button in the popin
    And I wait to be on the "t-shirt" product page
    And I visit the "Marketing" group
    And I switch the scope to "tablet"
    And I change the "Number in stock" to "42"
    And I should see the text "There are unsaved changes."
    And I save the product
    And I should not see the text "There are unsaved changes."
    And the history of the product "t-shirt" has been built
    When I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "t-shirt" should have the following values:
    | number_in_stock-tablet |  |
