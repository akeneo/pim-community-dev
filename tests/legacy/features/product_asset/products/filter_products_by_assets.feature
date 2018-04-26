@javascript
Feature: Filter products by assets
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products by assets

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku   | family |
      | shirt | tees   |
      | vest  |        |
    And I am logged in as "Julia"
    And I am on the "shirt" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    And I check the row "paint"
    And I confirm the asset modification
    And I save the product

  Scenario: Successfully filter products by assets
    Given I am on the products grid
    And the grid should contain 2 elements
    When I should be able to use the following filters:
      | filter     | operator | value        | result |
      | front_view | in list  | paint        | shirt  |
      | front_view | in list  | paint, akene | shirt  |
