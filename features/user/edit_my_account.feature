@javascript
Feature: Change my profile
  In order to change my profile
  As an administrator
  I need to be able to change my profile informations

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully change avatar
    Given I edit the "admin" user
    When I attach file "akeneo.jpg" to "Avatar"
    And I save the user
    Then I should see the flash message "User saved"
    And I should not see the default avatar

  @jira https://akeneo.atlassian.net/browse/PIM-6258
  Scenario: Successfully edit my own profile even if permissions on profile edition are revoked
    Given I am on the "Administrator" role page
    When I visit the "Permissions" tab
    And I revoke rights to resources Edit users
    And I save the role
    And I am on the User profile edit page
    When I attach file "akeneo.jpg" to "Avatar"
    And I save the user
    Then I should see the flash message "User saved"

  @jira https://akeneo.atlassian.net/browse/PIM-6894
  Scenario: Successfully update my password with any characters
    Given I edit the "Peter" user
    When I visit the "Password" tab
    And I fill in the following information:
      | Current password      | Peter         |
      | New password          | Peter{}()/\@: |
      | New password (repeat) | Peter{}()/\@: |
    And I save the user
    Then I should not see the text "There are unsaved changes"
    When I logout
    And I am logged in as "Peter" with password Peter{}()/\@:
    Then I am on the dashboard page
