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
    And I visit the "Users" tab
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
    And I should see the text "Roles"
    Then I should not be able to access the edit "User" Role page
    When I logout
    And I am logged in as "Peter"
    Then I am on the Role index page

  @jira https://akeneo.atlassian.net/browse/PIM-6993
  Scenario: Successfully show pagination on users grid in group or role page
    Given the following users:
      | username | first_name | last_name | email            | password | enabled | roles              | groups  |
      | antoine  | Antoine    | Doe       | antoine@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | arnaud   | Arnaud     | Doe       | arnaud@doe.com   | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | pascale  | Pascale    | Doe       | pascale@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | helene   | Helene     | Doe       | helene@doe.com   | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | leopold  | Leopold    | Doe       | leopold@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | bernard  | Bernard    | Doe       | bernard@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | maxime   | Maxime     | Doe       | maxime@doe.com   | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | charles  | Charles    | Doe       | charles@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | aurelie  | Aurelie    | Doe       | aurelie@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | remi     | Remi       | Doe       | remi@doe.com     | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | laurent  | Laurent    | Doe       | laurent@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | alex     | Alex       | Doe       | alex@doe.com     | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | emmy     | Emmy       | Doe       | emmy@doe.com     | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | zoe      | Zoe        | Doe       | zoe@doe.com      | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | carole   | Carole     | Doe       | carole@doe.com   | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | mylene   | Mylene     | Doe       | mylene@doe.com   | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | robin    | Robin      | Doe       | robin@doe.com    | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | yvette   | Yvette     | Doe       | yvette@doe.com   | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | tatiana  | Tatiana    | Doe       | tatiana@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | ulric    | Ulric      | Doe       | ulric@doe.com    | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | amandin  | Amandin    | Doe       | amandin@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | lucifer  | Lucifer    | Doe       | lucifer@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | quentin  | Quentin    | Doe       | quentin@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | xavier   | Xavier     | Doe       | xavier@doe.com   | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | valerie  | Valerie    | Doe       | valerie@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
      | wilbert  | Wilbert    | Doe       | wilbert@doe.com  | psswd    | yes     | ROLE_ADMINISTRATOR | manager |
    When I edit the "Administrator" Role
    And I visit the "Users" tab
    Then the last page number should be 2
    When I edit the "Manager" UserGroup
    And I visit the "Users" tab
    Then the last page number should be 2
