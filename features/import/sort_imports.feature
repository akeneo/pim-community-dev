@javascript
Feature: Sort import profiles
  In order to sort import profiles in the catalog
  As a user
  I need to be able to sort import profiles by several columns in the catalog

  Scenario: Successfully sort imports
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    When I am on the imports page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, job, connector and status
