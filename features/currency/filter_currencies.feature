@javascript
Feature: Filter currencies
  In order to filter currencies in the catalog
  As a user
  I need to be able to filter currencies in the catalog

  Scenario: Successfully filter currencies
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the currencies page
    Then I should be able to use the following filters:
      | filter    | value | result      |
      | Code      | EU    | EUR and XEU |
      | Activated | yes   | USD and EUR |
