@javascript
Feature: Filter products by assets
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products by assets

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku   |
      | shirt |
      | vest  |
    And I am logged in as "Julia"
    And I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I check the row "paint"
    And I confirm the asset modification
    And I save the product

  Scenario: Successfully filter products by assets
    Given I am on the products page
    And the grid should contain 2 elements
    Then I should see the filter "Front view"
    When I should be able to use the following filters:
      | filter     | value                | result |
      | Front view | in list paint        | shirt  |
      | Front view | in list paint, akene | shirt  |
