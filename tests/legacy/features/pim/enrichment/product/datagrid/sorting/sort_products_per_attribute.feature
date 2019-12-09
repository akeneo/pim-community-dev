@javascript
Feature: Sort products per attributes
  In order to enrich my catalog
  As a regular user
  I need to be able to manually sort products per attributes

  Scenario: Successfully sort products by sku
    Given the "apparel" catalog configuration
    And the following products:
      | sku          | family  |
      | blue_shirt   | tshirts |
      | red_shirt    | tshirts |
      | green_shirt  | tshirts |
      | yellow_shirt | tshirts |
      | orange_shirt | tshirts |
    And I am logged in as "Mary"
    And I am on the products grid
    And the grid should contain 5 elements
    And I should be able to sort the rows by ID
