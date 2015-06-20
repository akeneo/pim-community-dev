@javascript
Feature: Filter products by boolean field
  In order to filter products by boolean attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Mary"

  @jira https://akeneo.atlassian.net/browse/PIM-3406
  Scenario: Successfully filter products by boolean value for boolean attributes
    Given the following products:
      | sku   | family  | handmade |
      | pants | tshirts | 1        |
      | shirt | tshirts | 0        |
      | shoes | tshirts | 0        |
      | hat   | tshirts | 0        |
      | socks | tshirts | 1        |
    And I am on the products page
    Then the grid should contain 5 elements
    And I should see products pants, shirt, shoes, hat and socks
    And I should be able to use the following filters:
      | filter   | value | result               |
      | Handmade | yes   | pants and socks      |
      | Handmade | no    | shirt, shoes and hat |

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
    And I am on the products page
    Then the grid should contain 5 elements
    And I should see products pants, shirt, shoes, hat and socks
    And I should be able to use the following filters:
      | filter | value    | result               |
      | Status | Enabled  | pants, shirt and hat |
      | Status | Disabled | shoes and socks      |
