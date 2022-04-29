@javascript @restore-product-feature-enabled
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product

  Scenario: Successfully revert a metric attribute
    When I fill in the following information in the popin:
      | SKU    | t-shirt |
      | Family | Jackets |
    And I press the "Save" button in the popin
    And I am on the "t-shirt" product page
    And I visit the "Sizes" group
    And I fill in "Length" with "70"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And I visit the "Sizes" group
    And I fill in "Length" with ""
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "t-shirt" has been built
    And I visit the "History" column tab
    Then I should see 3 versions in the history
    When I revert the product version number 2
    And I visit the "Attributes" column tab
    And I visit the "Sizes" group
    Then the product "t-shirt" should have the following values:
    | length | 70.0000 CENTIMETER |

  Scenario: Successfully revert an empty metric attribute
    When I fill in the following information in the popin:
      | SKU    | marcel  |
      | Family | Jackets |
    And I press the "Save" button in the popin
    And I am on the "marcel" product page
    And I visit the "Sizes" group
    And I fill in "Length" with "120"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "marcel" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    And I visit the "Attributes" column tab
    And I visit the "Sizes" group
    Then the product "marcel" should have the following values:
      | length |  |
