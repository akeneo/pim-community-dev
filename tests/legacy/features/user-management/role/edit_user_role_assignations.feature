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
    When I check the rows "Sandra"
    And I save the role
    And I should not see the text "There are unsaved changes."
    Then the row "Sandra" should be checked

  @jira https://akeneo.atlassian.net/browse/PIM-5201
  Scenario: Successfully remove a role from the group page
    Given I edit the "User" Role
    When I visit the "Permissions" tab
    And I grant rights to group System
    And I revoke rights to resource Edit roles
    And I save the Role
    Then I should not see the text "There are unsaved changes."
    When I logout
    And I am logged in as "Mary"
    And I am on the Role index page
    Then I should not be able to access the edit "User" Role page
    When I logout
    And I am logged in as "Peter"
    Then I am on the Role index page
