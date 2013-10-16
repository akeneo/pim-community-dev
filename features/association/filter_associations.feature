@javascript
Feature: Filter associations
  In order to easily find associations in the catalog
  As a user
  I need to be able to filter associations

  Background:
    Given the following associations:
      | code         | label        |
      | cross_sell   | Cross sell   |
      | up_sell      | Upsell       |
      | substitution | Substitution |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the associations page
    Then I should see the filters Code and Label
    And the grid should contain 3 elements
    And I should see associations cross_sell, up_sell and substitution

  Scenario: Successfully filter by code
    Given I am on the associations page
    When I filter by "Code" with value "up_sell"
    Then the grid should contain 1 element
    And I should see association up_sell
    And I should not see associations cross_sell and substitution

  Scenario: Successfully filter by label
    Given I am on the associations page
    When I filter by "Label" with value "sell"
    Then the grid should contain 2 elements
    And I should see associations up_sell and cross_sell
    And I should not see association substitution

  Scenario: Successfully filter by label and code
    Given I am on the associations page
    When I filter by "Code" with value "o"
    And I filter by "Label" with value "l"
    Then the grid should contain 1 element
    And I should see association cross_sell
    And I should not see associations up_sell and substitution
