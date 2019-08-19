@javascript
Feature: Change my profile
  In order to change my profile
  As an administrator
  I need to be able to change my profile informations

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  @jira https://akeneo.atlassian.net/browse/PIM-8286
  Scenario: I can edit my own profile even if I don't have the permission to edit users
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Edit users
    And I save the role
    When I edit the "Peter" user
    And I visit the "Groups and roles" tab
    Then the fields User groups and Roles should be disabled
    And I visit the "General" tab
    And I fill in the following information:
      | Middle name | James |
    And I save the user
    Then I should not see the text "There are unsaved changes"
    And the "Middle name" field should contain "James"

  @jira https://akeneo.atlassian.net/browse/PIM-6914
  Scenario: Successfully display the UI locale of the user
    Given I edit the "Peter" user
    When I visit the "Interfaces" tab
    And I fill in the following information:
      | UI locale (required) | French (France) |
    And I save the user
    Then I should not see the text "There are unsaved changes"
    And I should see the text "français (France)"
    Given I edit the "Mary" user
    When I visit the "Interfaces" tab
    And I fill in the following information:
      | Langue de l'interface (obligatoire) | français (France) |
    And I save the user
    Then I should not see the text "There are unsaved changes"
    And I should see the text "français (France)"
