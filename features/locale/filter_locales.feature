@javascript
Feature: Filter locales
  In order to filter locales in the catalog
  As a user
  I need to be able to filter locales in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the locales page
    Then I should see the filters Code and Activated

  Scenario: Successfully filter by code
    Given I filter by "Code" with value "as"
    Then the grid should contain 1 element
    And I should see locale as_IN

  Scenario: Successfully filter by activated
    Given I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see locales en_US and fr_FR
    When I filter by "Activated" with value "no"
    And I filter by "Code" with value "de_DE"
    Then the grid should contain 1 element
    And I should see locale de_DE
