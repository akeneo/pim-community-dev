@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert a text attribute
    Given the following product:
    | sku     | family | comment            |
    | t-shirt | tees   | This is a comment. |
    | marcel  | tees   |                    |
    Given I am on the "t-shirt" product page
    And I visit the "Other" group
    And I change the "Comment" to "This is not a comment anymore."
    And I save the product
    And the history of the product "t-shirt" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "t-shirt" should have the following values:
    | comment | This is a comment. |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    And I add available attributes Comment
    And I visit the "Product information" group
    And I change the "Name" to "test"
    And I visit the "Other" group
    And I change the "Comment" to "New comment."
    And I save the product
    And the history of the product "marcel" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "marcel" should have the following values:
    | comment |  |
