@javascript
Feature: Browse users
  In order to manage the users and rights
  As Peter
  I need to be able to see users

  Background:
    Given I am logged in as "admin"

  Scenario: Successfully display users
    Given I am on the users page
    Then the grid should contain 1 element
    When I click on the "View" action of the row which contains "admin"
    Then I should see "Doe, John"

