@javascript
Feature: Browse locales
  In order to check wether or not a locale is available is the catalog
  As an administrator
  I need to be able to see active locales in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully view locales
    When I am on the locales page
    And the grid should contain 2 elements
    And I should see entity en_US and fr_FR
