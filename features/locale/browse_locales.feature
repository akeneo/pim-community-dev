@javascript
Feature: Browse locales
  In order to check wether or not a locale is available is the catalog
  As an administrator
  I need to be able to see active and inactive locales in the catalog

  Scenario: Successfully view, sort and filter locales
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    When I am on the locales page
    Then I should see the columns Code and Activated
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code and activated
    And I should be able to use the following filters:
      | filter    | value | result          |
      | Code      | as    | as_IN           |
      | Activated | yes   | en_US and fr_FR |
