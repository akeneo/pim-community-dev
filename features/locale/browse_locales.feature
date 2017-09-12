@javascript
Feature: Browse locales
  In order to check wether or not a locale is available is the catalog
  As an administrator
  I need to be able to see active and inactive locales in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    When I am on the locales grid
    Then I should see the columns Code and Activated
    And the rows should be sorted ascending by Code

  Scenario: Successfully view and sort and filter locales
    Then I should be able to sort the rows by Code and Activated
    When I show the filter "activated"
    And I filter by "activated" with operator "" and value "yes"
    Then the grid should contain 2 elements
    Then I should see entity en_US and fr_FR

  Scenario: Successfully search on label
    When I search "as"
    Then the grid should contain 1 element
    Then I should see entity as_IN
