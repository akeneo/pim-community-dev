@javascript
Feature: Browse associations
  In order to list the existing associations in the catalog
  As a user
  I need to be able to see associations

  Scenario: Successfully display associations
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the associations page
    Then the grid should contain 4 elements
    And I should see the columns Code and Label
    And I should see associations X_SELL, UPSELL, SUBSTITUTION and PACK
