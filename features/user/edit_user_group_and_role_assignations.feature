@javascript
Feature: Edit a user groups and roles
  In order to manage the users and rights
  As an administrator
  I need to be able to modify the user's groups and roles assignations

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully change a user group
    Given I edit the "admin" user
    And I visit the "Groups and Roles" tab
    And I check "Redactor"
    And I save the user
    Then I should not see the text "There are unsaved changes."
    When I refresh current page
    And I edit the "admin" user
    And I visit the "Groups and Roles" tab
    Then the "Redactor" checkbox should be checked
    And the "IT support" checkbox should be checked
    When I uncheck "IT support"
    And I uncheck "Redactor"
    And I save the user
    Then I should not see the text "There are unsaved changes."
    When I refresh current page
    And I edit the "admin" user
    And I visit the "Groups and Roles" tab
    And the "Redactor" checkbox should not be checked
    And the "IT support" checkbox should not be checked
    But the "Administrator" checkbox should be checked

  Scenario: Successfully change a user role
    Given I edit the "admin" user
    And I visit the "Groups and Roles" tab
    And I check "User"
    And I save the user
    Then the "User" checkbox should be checked
    And the "Administrator" checkbox should be checked
    When I edit the "admin" user
    Then I should not see the text "There are unsaved changes."
    And I visit the "Groups and Roles" tab
    And I uncheck "Administrator"
    And I uncheck "User"
    And I save the user
    Then I should not see the text "There are unsaved changes."
    And I visit the "Groups and Roles" tab
    And the user "admin" should still have 2 roles
    And the "User" checkbox should be checked
    And the "Administrator" checkbox should be checked

  Scenario: Assign a group to a user from the group page
    Given I edit the "Redactor" user group
    And I visit the "Users" tab
    When I check the rows "Peter"
    And I save the group
    And I visit the "Users" tab
    Then the row "Peter" should be checked

  Scenario: Assign a role to a user from the role page
    Given I edit the "Catalog manager" role
    And I visit the "Users" tab
    When I check the rows "Peter"
    And I save the role
    Then the row "Peter" should be checked

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
