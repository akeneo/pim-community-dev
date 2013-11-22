@javascript
Feature: Filter locales
  In order to filter locales in the catalog
  As a user
  I need to be able to filter locales in the catalog

  Scenario: Successfully filter locales
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the locales page
    Then I should be able to use the following filters:
      | filter    | value | result          |
      | Code      | as    | as_IN           |
      | Activated | yes   | en_US and fr_FR |
