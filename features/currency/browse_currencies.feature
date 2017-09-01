@javascript
Feature: Browse currencies
  In order to check whether or not a currency is available in the catalog
  As an administrator
  I need to be able to see active and inactive currencies in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the currencies grid

  Scenario: Successfully view and sort currencies
    Then I should see the columns Code and Activated
    And the rows should be sorted ascending by Code

  Scenario: Successfully filter currencies
    When I show the filter "activated"
    And I filter by "activated" with operator "" and value "yes"
    Then the grid should contain 2 elements
    Then I should see entity USD and EUR

  Scenario: Successfully search on code
    When I search "EU"
    Then the grid should contain 2 elements
    And I should see entities EUR and XEU

  Scenario: Successfully activate a currency
    Given I search "GBP"
    And I activate the GBP currency
    And I search " "
    Then the grid should contain 1 element
    When I filter by "activated" with operator "equals" and value "yes"
    Then the grid should contain 3 elements
    Then I should see currencies GBP, USD and EUR

  Scenario: Successfully deactivate a currency
    Given I activate the AED currency
    Given I filter by "activated" with operator "equals" and value "yes"
    Then the grid should contain 3 elements
    And I should see currencies USD, AED and EUR
    When I deactivate the AED currency
    Then the grid should contain 2 element
    And I should see currency USD and EUR

  @jira https://akeneo.atlassian.net/browse/PIM-4488
  Scenario: Cannot deactivate a currency linked to a channel
    Given I filter by "activated" with operator "equals" and value "yes"
    Then the grid should contain 2 elements
    And I should see currencies USD and EUR
    When I deactivate the USD currency
    Then the grid should contain 2 element
    And I should see currencies USD and EUR
