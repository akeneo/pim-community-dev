@javascript
Feature: Browse association types
  In order to list the existing association types in the catalog
  As a product manager
  I need to be able to see association types

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the association types page
    And the grid should contain 4 elements

  Scenario: Successfully view, sort and filter association types
    And I should see the columns Code and Label
    And I should see association types X_SELL, UPSELL, SUBSTITUTION and PACK
    And the rows should be sorted ascending by Code
    And I should be able to sort the rows by Code and Label

  Scenario: Successfully filter association types
    When I show the filter "code"
    And I filter by "code" with operator "contains" and value "UP"
    Then the grid should contain 1 element
    Then I should see entity UPSELL

  Scenario: Successfully search on label
    When I search "sell"
    Then the grid should contain 2 elements
    And I should see entity X_SELL and UPSELL
