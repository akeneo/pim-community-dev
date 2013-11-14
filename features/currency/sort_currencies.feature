@javascript
Feature: Sort currencies
  In order to sort currencies in the catalog
  As a user
  I need to be able to sort currencies by several columns in the catalog

  Scenario: Successfully sort currencies
    And I am logged in as "admin"
    Given I am on the currencies page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code and activated
