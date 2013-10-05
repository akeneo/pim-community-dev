@javascript
Feature: Browse roles
  In order to manage the user roles and rights
  As Peter
  I need to be able to see user roles

  Background:
    Given I am logged in as "Peter"

  Scenario: Successfully display roles
    Given I am on the user roles page
    Then the grid should contain 3 elements
    When I click on the "Update" action of the row which contains "User"
    Then I should see "User"
