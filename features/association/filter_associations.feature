@javascript
Feature: Filter associations
  In order to easily find associations in the catalog
  As a user
  I need to be able to filter associations

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the associations page

  Scenario: Successfully display filters
    Then I should see the filters Code and Label
    And the grid should contain 4 elements
    And I should see associations X_SELL, UPSELL, SUBSTITUTION and PACK

  Scenario: Successfully filter by code
    When I filter by "Code" with value "UP"
    Then the grid should contain 1 element
    And I should see association UPSELL
    And I should not see associations X_SELL, SUBSTITUTION and PACK

  Scenario: Successfully filter by label
    When I filter by "Label" with value "sell"
    Then the grid should contain 2 elements
    And I should see associations X_SELL and UPSELL
    And I should not see associations SUBSTITUTION and PACK

  Scenario: Successfully filter by label and code
    When I filter by "Code" with value "l"
    And I filter by "Label" with value "o"
    Then the grid should contain 1 element
    And I should see association X_SELL
    And I should not see associations UPSELL, SUBSTITUTION and PACK
