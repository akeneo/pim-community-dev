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
    And I save the product
    And the history of the product "jean" has been built
    And I open the history
    Then I should see 2 versions in the history
    And I should see history:
      | version | property     | value    |
      | 2       | Manufacturer | Desigual |
      | 1       | SKU          | jean     |
      | 1       | family       | pants    |
      | 1       | enabled      | 1        |
    When I revert the product version number 1
    Then I should see 3 versions in the history
    Then I should see history:
      | version | property     | value    |
      | 3       | Manufacturer |          |
      | 2       | Manufacturer | Desigual |
      | 1       | SKU          | jean     |
      | 1       | family       | pants    |
      | 1       | enabled      | 1        |
    When I visit the "Attribute" tab
    Then the product "jean" should have the following values:
      | Manufacturer | |

  Scenario: Successfully revert a simpleselect attribute
    Given the following product:
    | sku     | family | rating |
    | t-shirt | tees   | 4      |
    | marcel  | tees   |        |
    When I am on the "t-shirt" product page
    And I visit the "Marketing" group
    And I change the "Rating" to "2"
    And I save the product
    And the history of the product "t-shirt" has been built
    And I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "t-shirt" should have the following values:
    | rating | [4] |
    When I am on the "marcel" product page
    And I visit the "Attributes" tab
    And I visit the "Product information" group
    And I change the "Name" to "test"
    And I visit the "Marketing" group
    And I change the "Rating" to "5"
    And I save the product
    And the history of the product "marcel" has been built
    And I open the history
    Then I should see 2 versions in the history
    When I revert the product version number 1
    Then the product "marcel" should have the following values:
    | rating |  |
