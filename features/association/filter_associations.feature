@javascript
Feature: Filter associations
  In order to easily find associations in the catalog
  As a user
  I need to be able to filter associations

  Scenario: Successfully filter associations
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the associations page
    Then the grid should contain 4 elements
    And I should see associations X_SELL, UPSELL, SUBSTITUTION and PACK
    And I should be able to use the following filters:
      | filter | value | result            |
      | Code   | UP    | UPSELL            |
      | Label  | sell  | X_SELL and UPSELL |
