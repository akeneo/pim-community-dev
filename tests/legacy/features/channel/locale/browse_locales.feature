@javascript
Feature: Browse locales
  In order to check wether or not a locale is available is the catalog
  As an administrator
  I need to be able to see active and inactive locales in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    When I am on the locales page
    Then I should see the columns Code and Activated

  Scenario: Successfully view and sort and filter locales
    When I show the filter "activated"
    And I filter by "activated" with operator "" and value "yes"
    Then the grid should contain 2 elements
    Then I should see entity en_US and fr_FR
