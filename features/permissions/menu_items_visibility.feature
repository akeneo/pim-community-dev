@javascript
Feature: Check menu items visibility
  In order to be able to prevent some users from viewing empty menu
  As an administrator or a user
  I need to be able to check the visibility of menu items

  Scenario: Check the System menu visibility
    Given a "default" catalog configuration
    And I am logged in as "Sandra"
    When I am on the dashboard page
    Then I should see the text "System"
    When I am on the "User" role page
    And I remove rights to List users, Create a user, Edit users and Remove a user
    And I remove rights to List roles, Create a role, Edit roles and Remove a role
    And I remove rights to List user groups, Create a user group, Edit user groups and Remove a user group
    And I remove rights to System configuration
    And I remove rights to System information
    And I remove rights to View process tracker
    And I save the role
    And I am on the dashboard page
    Then I should not see the text "System"
