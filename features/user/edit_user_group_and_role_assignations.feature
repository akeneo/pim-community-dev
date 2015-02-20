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
    Then I should see "User saved"
    # 3 groups because of 'all' user_role
    And the user "admin" should be in 3 groups
    And the user "admin" should be in the "Redactor" group

    Given I edit the "admin" user
    And I visit the "Groups and Roles" tab
    When I uncheck "IT support"
    And I uncheck "Redactor"
    And I save the user
    Then I should see "User saved"
    And the user "admin" should be in 1 group


  Scenario: Successfully change a user role
    Given I edit the "admin" user
    And I visit the "Groups and Roles" tab
    And I check "User"
    And I save the user
    Then I should see "User saved"
    And the user "admin" should have 2 roles
    And the user "admin" should have the "ROLE_USER" role

    Given I edit the "admin" user
    And I visit the "Groups and Roles" tab
    And I uncheck "Administrator"
    And I uncheck "User"
    And I save the user
    Then the user "admin" should still have 2 roles
    And the user "admin" should have the "ROLE_ADMINISTRATOR" role
    And the user "admin" should have the "ROLE_USER" role

