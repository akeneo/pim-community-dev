@javascript
Feature: Create a user
  In order to manage the users and rights
  As an administrator
  I need to be able to create a user

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Have English language as default language for new users
    Given I am on the user creation page
    And I visit the "Interfaces" tab
    And I should see "English (United States)"

  Scenario: Successfully create a user
    Given I am on the user creation page
    And I fill in the following information:
      | Username          | jack     |
      | First name        | Jack     |
      | Last name         | Doe      |
      | Password          | DoeDoe   |
      | Re-enter password | DoeDoe   |
      | Status            | Inactive |
    And I scroll down
    And I fill in the following information:
      | E-mail | jack+doe@example.com |
    And I visit the "Groups and Roles" tab
    And I select the role "User"
    When I save the user
    Then there should be a "jack" user

  Scenario: Fail to create a user with an invalid email address
    Given I am on the user creation page
    And I fill in the following information:
      | Username          | jack     |
      | First name        | Jack     |
      | Last name         | Doe      |
      | Password          | DoeDoe   |
      | Re-enter password | DoeDoe   |
      | Status            | Inactive |
    And I scroll down
    And I fill in the following information:
      | E-mail | jack..doe@example.com |
    And I visit the "Groups and Roles" tab
    And I select the role "User"
    When I save the user
    Then I should see a validation tooltip "This value is not a valid email address."

  Scenario: Successfully delete a user from grid
    Given I am on the users page
    When I click on the "Delete" action of the row which contains "Julia"
    Then I should see a confirm dialog with the following content:
      | title   | Delete Confirmation                        |
      | content | Are you sure you want to delete this user? |
    When I confirm the deletion
    Then I should not see "Julia"

  Scenario: Successfully delete a user from user page
    Given I am on the users page
    When I click on the "View" action of the row which contains "Julien"
    And I should see the text "Julien Février"
    Then I press the secondary action "Delete"
    Then I should see a confirm dialog with the following content:
      | title   | Delete Confirmation                        |
      | content | Are you sure you want to delete this user? |
    When I confirm the deletion
    Then I should not see "Julien"
