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
    And I should see users "admin", "Peter", "Julia", "Mary" and "Sandra"
    When I click on the "Edit" action of the row which contains "Peter"
    Then I should see the text "Peter"
