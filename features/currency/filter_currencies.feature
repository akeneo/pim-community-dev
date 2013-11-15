@javascript
Feature: Filter currencies
  In order to filter currencies in the catalog
  As a user
  I need to be able to filter currencies in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the currencies page
    Then I should see the filters "Code" and "Activated"

  Scenario: Successfully filter by code
    Given I filter by "Code" with value "EU"
    Then the grid should contain 2 elements
    And I should see currencies EUR and XEU

  Scenario: Successfully filter by activated
    Given I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see currencies USD and EUR
    When I filter by "Activated" with value "no"
    Then I should not see currencies USD and EUR
