@javascript
Feature: Edit a user groups and roles
  In order to manage the users and rights
  As an administrator
  I need to be able to modify the user's groups and roles assignations

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully change a user role
    Given I edit the "admin" user
    And I visit the "Groups and roles" tab
    And I fill in the following information:
      | Roles | Administrator, User |
    And I save the user
    And the user "admin" should have 2 roles
    When I edit the "admin" user
    Then I should not see the text "There are unsaved changes."
    And I visit the "Groups and roles" tab
    And I fill in the following information:
      | Roles | |
    And I save the user
    Then I should see the text "There are unsaved changes."
    And I should see the text "You must select at least 1 role"
    And the user "admin" should still have 2 roles

  Scenario: Assign a role to a user from the role page
    Given I edit the "Catalog manager" role
    And I visit the "Users" tab
    When I check the rows "Peter"
    And I save the role
    And I should not see the text "There are unsaved changes."
    Then the row "Peter" should be checked
