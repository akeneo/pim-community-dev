@javascript
Feature: Browse currencies
  In order to check wether or not a currency is available in the catalog
  As a user
  I need to be able to see active and inactive currencies in the catalog

  Background:
    Given the following currencies:
      | code | activated |
      | USD  | yes       |
      | EUR  | yes       |
      | GBP  | no        |
    And I am logged in as "admin"

  Scenario: Successfully filter by code
    Given I am on the currencies page
    Then the grid should contain 3 elements
    And I should see currencies GBP, USD and EUR
    And I should see the filters "Code" and "Activated"
    When I filter by "Code" with value "U"
    Then the grid should contain 2 elements
    And I should see currencies USD and EUR

  Scenario: Successfully filter by code
    Given I am on the currencies page
    Then the grid should contain 3 elements
    And I should see currencies GBP, USD and EUR
    And I should see the filters "Code" and "Activated"
    When I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see currencies USD and EUR

  Scenario: Successfully filter by code
    Given I am on the currencies page
    Then the grid should contain 3 elements
    And I should see currencies GBP, USD and EUR
    And I should see the filters "Code" and "Activated"
    When I filter by "Activated" with value "no"
    Then the grid should contain 1 element
    And I should see currencies GBP
