@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a product number and leave it empty
    And the following product:
    | sku   | family  |
    | jeans | jackets |
    When I edit the "jeans" product
    And I visit the "Marketing" group
    And I switch the scope to "tablet"
    And I change the "Number in stock" to "100"
    And I save the product
    And the history of the product "jeans" has been built
    And I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    And I save the product
    And the product "jeans" should have the following values:
    | number_in_stock-tablet |  |

  Scenario: Successfully revert a number attribute
    Given the following product:
    | sku     | family |
    | t-shirt | tees   |
    Given I am on the "t-shirt" product page
    And I add available attributes Number in stock
    And I visit the "Marketing" group
    And I switch the scope to "tablet"
    And I change the "Number in stock" to "42"
    And I save the product
    And the history of the product "t-shirt" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "t-shirt" should have the following values:
    | number_in_stock-tablet |  |
