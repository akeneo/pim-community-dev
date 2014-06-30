@javascript
Feature: Browse association types
  In order to list the existing association types in the catalog
  As a product manager
  I need to be able to see association types

  Scenario: Successfully view, sort and filter association types
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the association types page
    Then the grid should contain 4 elements
    And I should see the columns Code and Label
    And I should see association types X_SELL, UPSELL, SUBSTITUTION and PACK
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code and label
    And I should be able to use the following filters:
      | filter | value | result            |
      | Code   | UP    | UPSELL            |
      | Label  | sell  | X_SELL and UPSELL |
