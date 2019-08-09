@javascript
Feature: Filter products
  In order to filter products in the catalog per completeness
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "footwear" catalog configuration
    And the following family:
      | code   | attributes             | requirements-tablet |
      | family | color,description,name | color,name          |
    And the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1 |
      | family_variant | family | color          | description          |
    And the following root product models:
      | code               | family_variant | name-en_US |
      | code-product-model | family_variant | name       |
    And the following products:
      | sku      | family | parent             | color |
      | product1 | family | code-product-model | black |
      | product2 | family | code-product-model | red   |
      | product3 | family |                    | black |
    And I am logged in as "Mary"
    And I am on the products grid
    And I switch the locale to "en_US"
    And I switch the scope to "tablet"

  @critical
  Scenario: Filter incomplete products and product model
    When I filter by "completeness" with operator "" and value "no"
    Then I should see products product3

  @critical
  Scenario: Successfully filter complete products
    When I filter by "completeness" with operator "" and value "yes"
    Then I should see products code-product-model
