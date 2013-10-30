@javascript
Feature: Sort currencies
  In order to sort currencies in the catalog
  As a user
  I need to be able to sort currencies by several columns in the catalog

  Background:
    Given the following currencies:
      | code | activated |
      | USD  | yes       |
      | EUR  | yes       |
      | GBP  | no        |
    And I am logged in as "admin"

  Scenario: Successfully sort currencies
    Given I am on the currencies page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code and activated
