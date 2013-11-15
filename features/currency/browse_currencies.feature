@javascript
Feature: Browse currencies
  In order to check wether or not a currency is available in the catalog
  As a user
  I need to be able to see active and inactive currencies in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the currencies page

  Scenario: Successfully display currencies
    Given I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see activated currencies USD and EUR
    And I should see the columns Code, Label and Activated

  Scenario: Successfully activate a currency
    Given I filter by "Code" with value "GBP"
    And I activate the GBP currency
    When I hide the filter "Code"
    And I filter by "Activated" with value "yes"
    Then I should see activated currencies GBP, USD and EUR

  Scenario: Successfully deactivate a currency
    Given I filter by "Activated" with value "yes"
    Then I should see activated currencies USD and EUR
    When I deactivate the USD currency
    Then the grid should contain 1 element
    And I should see activated currency EUR
