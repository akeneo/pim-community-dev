@javascript
Feature: Filter currencies
  In order to filter currencies in the catalog
  As a user
  I need to be able to filter currencies in the catalog

  Background:
    Given the following currencies:
      | code | activated |
      | USD  | yes       |
      | EUR  | yes       |
      | GBP  | no        |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the currencies page
    Then I should see the filters "Code" and "Activated"

  Scenario: Successfully filter by code
    Given I am on the currencies page
    Then the grid should contain 3 elements
    And I should see currencies GBP, USD and EUR
    And I should see the filters "Code" and "Activated"
    When I filter by "Code" with value "U"
    Then the grid should contain 2 elements
    And I should see currencies USD and EUR

  Scenario: Successfully filter by activated "yes"
    Given I am on the currencies page
    Then the grid should contain 3 elements
    And I should see currencies GBP, USD and EUR
    And I should see the filters "Code" and "Activated"
    When I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see currencies USD and EUR

  Scenario: Successfully filter by activated "no"
    Given I am on the currencies page
    Then the grid should contain 3 elements
    And I should see currencies GBP, USD and EUR
    And I should see the filters "Code" and "Activated"
    When I filter by "Activated" with value "no"
    Then the grid should contain 1 element
    And I should see currencies GBP
