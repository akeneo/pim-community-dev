@javascript
Feature: Update user preferences
  In order for users to be able to choose their preferences
  As a user
  I need to be able to setup my preferences

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

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
    And I logout
    And I am logged in as "Peter"
    When I edit the "Peter" user
    Then I should see the text "Edit user"
