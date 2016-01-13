@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a boolean attribute
    Given the following product:
    | sku   | family | handmade |
    | jeans | pants  | 1        |
    | short | pants  |          |
    Given I am on the "jeans" product page
    When I uncheck the "Handmade" switch
    And I save the product
    And the history of the product "jeans" has been built
    And I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "jeans" should have the following values:
    | handmade | 1 |
    Given I am on the "short" product page
    And I visit the "Attributes" tab
    And I add available attributes Handmade
    When I check the "Handmade" switch
    And I save the product
    And the history of the product "short" has been built
    And I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    And the product "short" should have the following values:
    | handmade |  |
