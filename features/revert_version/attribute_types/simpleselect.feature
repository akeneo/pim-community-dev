@javascript
Feature: Revert product attributes to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert product attributes to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert simpleselect attribute options of a product
    Given the following product:
    | sku  | family |
    | jean | pants  |
    Given I am on the "jean" product page
    And I change the Manufacturer to "Desigual"
    Then I save the product
    And the history of the product "jean" has been built
    And I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then I should see a flash message "Product successfully restored"

  Scenario: Successfully revert a simpleselect attribute
    Given the following product:
    | sku     | family | rating |
    | t-shirt | tees   | 4      |
    | marcel  | tees   |        |
    Given I am on the "t-shirt" product page
    And I visit the "Marketing" group
    And I change the "Rating" to "2"
    And I save the product
    And the history of the product "t-shirt" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "t-shirt" should have the following values:
    | rating | [4] |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    And I visit the "Product information" group
    And I change the "Name" to "test"
    And I visit the "Marketing" group
    And I change the "Rating" to "5"
    And I save the product
    And the history of the product "marcel" has been built
    When I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "marcel" should have the following values:
    | rating |  |
