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
    And I visit the "Permissions" tab
    And I revoke rights to group System
    And I save the role
    And I should not see the text "There are unsaved changes."
    And I should not see the text "Loading"
    Then I should not see the text "System"
    And I should see the text "You have no current project"
