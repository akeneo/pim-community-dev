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

  Scenario: Successfully search on label
    When I search "sell"
    Then the grid should contain 2 elements
    And I should see entity Cross sell and Upsell
