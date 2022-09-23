@javascript
Feature: Update user preferences
  In order for users to be able to choose their preferences
  As a user
  I need to be able to setup my preferences

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  # @jira https://akeneo.atlassian.net/browse/PIM-5434
  Scenario: Editing user profile without access to any category
    Given the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | IT support | none   |
      | 2015_collection  | IT support | none   |
      | 2013_collection  | IT support | none   |
    And I logout
    And I am logged in as "Peter"
    When I edit the "Peter" user
    Then I should see the text "Save"

  # @jira https://akeneo.atlassian.net/browse/PIM-6470
  @javascript
  Scenario: Add permissions filter on default grid filters
    Given I edit the "Julia" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Product grid filters | Permissions |
    And I save the user
    When I am on the users page
    And I click on the "Update" action of the row which contains "Julia"
    And I visit the "Additional" tab
    Then I should see the text "Permissions"
