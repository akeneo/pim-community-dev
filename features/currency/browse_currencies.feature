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

  Scenario: Successfully display currencies
    Given I am on the currencies page
    Then I should see activated currencies USD and EUR
    And I should see deactivated currency GBP

  Scenario: Successfully display filters
    Given I am on the currencies page
    Then I should see the filters "Code" and "Activated"

  Scenario: Successfully display columns
    Given I am on the currencies page
    Then the grid should contain 3 elements
    And I should see the columns Code and Activated

  Scenario: Successfully activate a currency
    Given I am on the currencies page
    When I activate the GBP currency
    Then I should see activated currencies GBP, USD and EUR

  Scenario: Successfully deactivate a currency
    Given I am on the currencies page
    When I deactivate the USD currency
    Then I should see activated currency EUR
    And I should see deactivated currencies GBP and USD
