@acceptance-back
Feature: Update a table attribute
  In order to structure my catalog
  As a catalog manager
  I need to be able to update a table attribute

  Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR
    And a valid table attribute

  Scenario: Can update a table attribute
    When I update a table attribute with a valid configuration
    Then There is no violation
    And the attribute contains the "new_column" column
    And the attribute does not contain the "isAllergenic" column

  Scenario: Cannot update a table attribute when the first column code changes
    When I change a table attribute updating the first column code
    Then There is a violation with message: The code of your first column cannot be changed.
