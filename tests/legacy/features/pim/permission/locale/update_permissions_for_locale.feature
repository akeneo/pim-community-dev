@javascript @permission-feature-enabled
Feature: Update permissions for a locale
  In order to be able to allow locale for some specific user group
  As an administrator
  I need to be able to update permissions for locales

  Scenario: Updating pim permissions for a locale does not update app permissions for this locale
    Given a "clothing" catalog configuration
    And an user group "my_app" with type "app"
    And the following locale accesses:
      | locale | user group | access |
      | de_DE  | Manager    | edit   |
      | de_DE  | my_app     | edit   |
    And I am logged in as "Mary"
    When I am on the "de_DE" locale page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view information | IT support |
    And I save the locale
    And I should not see the text "There are unsaved changes."
    Then user group "my_app" should have the following locale permissions:
      | locale | accesses   |
      | de_DE  | edit, view |
