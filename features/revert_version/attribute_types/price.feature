@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3351
  Scenario: Successfully revert a product with prices and leave them empty
    When I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | jeans |
      | Family | Pants |
    And I press the "Save" button in the popin
    And I wait to be on the "jeans" product page
    And I visit the "Marketing" group
    And I change the "Price" to "42 EUR"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "jeans" has been built
    And I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    And I visit the "Attributes" column tab
    And I visit the "Marketing" group
    Then the product "jeans" should have the following values:
    | price |  |

  Scenario: Successfully revert a price attribute
    When I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | t-shirt |
      | Family | Tees    |
    And I press the "Save" button in the popin
    And I wait to be on the "t-shirt" product page
    And I visit the "Marketing" group
    And I change the "Price" to "49 EUR"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And I visit the "Marketing" group
    And I change the "Price" to "39 EUR"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "t-shirt" has been built
    When I visit the "History" column tab
    Then I should see 3 versions in the history
    When I revert the product version number 2
    Then the product "t-shirt" should have the following values:
    | price | 49.00 EUR |

  Scenario: Successfully revert a price attribute with empty value
    When I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | marcel |
      | Family | Tees   |
    And I press the "Save" button in the popin
    And I wait to be on the "marcel" product page
    Given I am on the "marcel" product page
    And I visit the "Attributes" column tab
    And I visit the "Marketing" group
    And I change the "Price" to "19.99 EUR"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And the history of the product "marcel" has been built
    When I visit the "History" column tab
    Then I should see 2 versions in the history
    When I revert the product version number 1
    And I visit the "Attributes" column tab
    And I visit the "Marketing" group
    Then the product Price should be ""
