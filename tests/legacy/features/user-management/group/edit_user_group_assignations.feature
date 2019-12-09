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
    And I visit the "Groups and roles" tab
    When I fill in the following information:
      | User groups | Redactor, IT support |
    And I save the user
    Then I should not see the text "There are unsaved changes."
    When I refresh current page
    And I edit the "admin" user
    And I visit the "Groups and roles" tab
    Then the field User groups should contain "Redactor, IT support"
    When I fill in the following information:
      | User groups | Redactor |
    And I save the user
    Then I should not see the text "There are unsaved changes."
    When I refresh current page
    And I edit the "admin" user
    And I visit the "Groups and roles" tab
    Then the field User groups should contain "Redactor"

  Scenario: Assign a group to a user from the group page
    Given I edit the "Redactor" user group
    And I visit the "Users" tab
    When I check the rows "Peter"
    And I save the group
    And I should not see the text "There are unsaved changes"
    And I visit the "Users" tab
    Then the row "Peter" should be checked
