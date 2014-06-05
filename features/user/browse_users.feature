@javascript
Feature: Browse users
  In order to manage the users and rights
  As an administrator
  I need to be able to see users

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully display users
    Given I am on the users page
    Then the grid should contain 3 elements
    And I should see users "admin", "Peter" and "Julia"
    When I click on the "View" action of the row which contains "Peter"
    Then I should see "Peter Doe"
