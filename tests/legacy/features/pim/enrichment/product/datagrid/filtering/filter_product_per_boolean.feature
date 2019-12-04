@javascript
Feature: Filter products by boolean field
  In order to filter products by boolean attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Mary"

  @critical  @jira https://akeneo.atlassian.net/browse/PIM-3406
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
