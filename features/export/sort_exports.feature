@javascript
Feature: Sort export profiles
  In order to sort export profiles in the catalog
  As a user
  I need to be able to sort export profiles by several columns in the catalog

  Scenario: Successfully sort exports
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the exports page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, job, connector and status
