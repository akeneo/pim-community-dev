@javascript
Feature: Sort attributes
  In order to sort attributes in the catalog
  As a user
  I need to be able to sort attributes by several columns in the catalog

  Scenario: Successfully sort attributes
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the attributes page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, scopable, localizable and group
