@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a textarea attribute
    Given I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | marcel |
      | family | Tees   |
    And I press the "Save" button in the popin
    And I wait to be on the "marcel" product page
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | t-shirt |
      | family | Tees    |
    And I press the "Save" button in the popin
    And I wait to be on the "t-shirt" product page
    And I visit the "Product information" group
    And I switch the scope to "tablet"
    And I change the "Description" to "A nice t-shirt."
    And I save the product
    And I should not see the text "There are unsaved changes"
    And I change the "Description" to "A really nice t-shirt !"
    And I save the product
    And the history of the product "t-shirt" has been built
    When I open the history
    Then I should see 3 versions in the history
    When I revert the product version number 2
    Then the product "t-shirt" should have the following values:
    | description-en_US-tablet | A nice t-shirt. |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    And I visit the "Product information" group
    And I change the "Name" to "test"
    And I switch the scope to "tablet"
    And I change the "Description" to "One does not simply fill a description."
    And I save the product
    And the history of the product "marcel" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "marcel" should have the following values:
    | comment |  |
