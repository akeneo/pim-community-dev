@javascript
Feature: Browse associations
  In order to list the existing associations in the catalog
  As a user
  I need to be able to see associations

  Scenario: Successfully view, sort and filter associations
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the associations page
    Then the grid should contain 4 elements
    And I should see the columns Code and Label
    And I should see associations X_SELL, UPSELL, SUBSTITUTION and PACK
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code and label
    And I should be able to use the following filters:
      | filter | value | result            |
      | Code   | UP    | UPSELL            |
      | Label  | sell  | X_SELL and UPSELL |
