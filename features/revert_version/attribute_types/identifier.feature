@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a identifier attribute
    Given the following product:
    | sku   | family |
    | jeans | pants  |
    Given I am on the "jeans" product page
    When I change the "SKU" to "pantalon"
    And I save the product
    And the history of the product "pantalon" has been built
    And I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "jeans" should have the following values:
    | sku | jeans |
