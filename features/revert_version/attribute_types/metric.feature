@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a metric attribute
    Given the following product:
    | sku     | family | length        |
    | t-shirt | tees   | 70 CENTIMETER |
    | marcel  | tees   |               |
    Given I am on the "t-shirt" product page
    And I visit the "Sizes" group
    When I change the "Length" to ""
    And I save the product
    And the history of the product "t-shirt" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "t-shirt" should have the following values:
    | length | 70.0000 CENTIMETER |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    Then I add available attributes Length
    And I visit the "Sizes" group
    When I change the "Length" to "120"
    And I save the product
    And the history of the product "marcel" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "marcel" should have the following values:
    | length |  |
