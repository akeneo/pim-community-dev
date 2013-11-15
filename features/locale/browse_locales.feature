@javascript
Feature: Browse locales
  In order to check wether or not a locale is available is the catalog
  As a user
  I need to be able to see active and inactive locales in the catalog

  Scenario: Successfully display locales
    Given the "default" catalog configuration
    And I am logged in as "admin"
    When I am on the locales page
    Then I should see the columns Code and Activated
    When I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see activated locales en_US and fr_FR
