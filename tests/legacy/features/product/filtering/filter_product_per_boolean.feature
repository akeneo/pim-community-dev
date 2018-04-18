@javascript
Feature: Filter products by boolean field
  In order to filter products by boolean attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Mary"

  @skip @info To be unskipped in PIM-6574 @jira https://akeneo.atlassian.net/browse/PIM-3406
  Scenario: Successfully filter products by boolean value for boolean attributes
    Given the following products:
      | sku   | family  | handmade |
      | pants | tshirts | 1        |
      | shirt | tshirts | 0        |
      | shoes | tshirts | 0        |
      | hat   | tshirts | 0        |
      | socks | tshirts | 1        |
    And I am on the products grid
    Then the grid should contain 5 elements
    And I should see products pants, shirt, shoes, hat and socks
    And I should be able to use the following filters:
      | filter   | operator | value | result               |
      | handmade |          | yes   | pants and socks      |
      | handmade |          | no    | shirt, shoes and hat |

  @jira https://akeneo.atlassian.net/browse/PIM-3406
  Scenario: Successfully filter products by boolean value for boolean attributes
    Given the following products:
      | sku   |
      | pants |
      | shirt |
      | shoes |
      | hat   |
      | socks |
    And an enabled "pants" product
    And an enabled "shirt" product
    And an enabled "hat" product
    And a disabled "shoes" product
    And a disabled "socks" product
    And I am on the products grid
    Then the grid should contain 5 elements
    And I should see products pants, shirt, shoes, hat and socks
    And I should be able to use the following filters:
      | filter  | operator | value    | result               |
      | enabled |          | Enabled  | pants, shirt and hat |
      | enabled |          | Disabled | shoes and socks      |

  @skip @info To be unskipped in PIM-6574 @jira https://akeneo.atlassian.net/browse/PIM-5334
  Scenario: Successfully filter products by boolean value for boolean attributes and refresh the grid
    Given the following products:
      | sku   | family  | handmade |
      | pants | tshirts | 1        |
      | shirt | tshirts | 0        |
      | shoes | tshirts | 0        |
      | hat   | tshirts | 0        |
      | socks | tshirts | 1        |
    And I am on the products grid
    Then the grid should contain 5 elements
    And I should see products pants, shirt, shoes, hat and socks
    When I show the filter "handmade"
    And I filter by "handmade" with operator "" and value "yes"
    Then the grid should contain 2 elements
    And I should see entities pants and socks
    When I reload the page
    And I am on the products grid
    And I show the filter "handmade"
    Then the grid should contain 2 elements
    And I should see entities pants and socks

  @skip @info To be unskipped in PIM-6574 @jira https://akeneo.atlassian.net/browse/PIM-5354
  Scenario: Keep boolean filter value after navigation
    Given the following products:
      | sku              | family  | handmade |
      | lumberjack-shirt | tshirts | 1        |
    And I am on the products grid
    And I show the filter "handmade"
    And I filter by "handmade" with operator "" and value "yes"
    When I am on the dashboard page
    And I am on the products grid
    Then the criteria of "handmade" filter should be "yes"
