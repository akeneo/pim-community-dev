@javascript
Feature: Browse currencies
  In order to check whether or not a currency is available in the catalog
  As an administrator
  I need to be able to see active and inactive currencies in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the currencies grid

  Scenario: Successfully activate a currency
    Given I search "GBP"
    Then the grid should contain 1 element
    And I activate the GBP currency
    And I search " "
    When I filter by "activated" with operator "equals" and value "yes"
    Then the grid should contain 3 elements
    Then I should see currencies GBP, USD and EUR
