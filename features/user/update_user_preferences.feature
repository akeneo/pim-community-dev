Feature: Update user preferences
  In order for users to be able to choose their preferences
  As a user
  I need to be able to setup my preferences

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully disable/enable email notifications
    Given I edit the "Julia" user
    And I visit the "Groups and Roles" tab
    And I check "Email notifications"
    And I save the user
    Then the user "Julia" should have email notifications enabled
    Then I edit the "Julia" user
    And I visit the "Groups and Roles" tab
    And I uncheck "Email notifications"
    And I save the user
    Then the user "Julia" should have email notifications disabled

  @javascript
  Scenario: Successfully set the delay before to send an asset expiration notification
    Given I edit the "Julia" user
    And I visit the "Groups and Roles" tab
    And I fill in "Asset delay reminder (in days)" with "12"
    And I save the user
    Then the user "Julia" should have an asset delay notification set to 12

  @jira https://akeneo.atlassian.net/browse/PIM-5434
  Scenario: Editing user profile without access to any category
    Given the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | IT support | none   |
      | 2015_collection  | IT support | none   |
      | 2013_collection  | IT support | none   |
    When I am logged in as "Peter"
    And I edit the "Peter" user
    Then I should see "Users"
    And I should see "Login count"

  @javascript @jira https://akeneo.atlassian.net/browse/PIM-6470
  Scenario: Add permissions filter on default grid filters
    Given I edit the "Julia" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Product grid filters | Permissions |
    And I save the user
    When I am on the users page
    And I click on the "View" action of the row which contains "Julia"
    And I visit the "Additional" tab
    Then I should see the text "Permissions"
