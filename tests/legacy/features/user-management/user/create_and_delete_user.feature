@javascript
Feature: Create a user
  In order to manage the users and rights
  As an administrator
  I need to be able to create a user

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully create a user
    Given I am on the users page
    And I press the "Create user" button and wait for modal
    And I fill in the following information:
      | Username   | jack                 |
      | First name | Jack                 |
      | Last name  | Doe                  |
      | Password   | DoeDoe               |
      | Email      | jack+doe@example.com |
    When I press the "Save" button
    Then there should be a "jack" user

  Scenario: Fail to create a user with an invalid email address
    Given I am on the users page
    And I press the "Create user" button and wait for modal
    And I fill in the following information:
      | Username   | jack                |
      | First name | Jack                |
      | Last name  | Doe                 |
      | Password   | DoeDoe              |
      | Email      | jack.doeexample.com |
    When I press the "Save" button
    Then I should see the text "This value is not a valid email address."

  Scenario: Successfully delete a user from grid
    Given I am on the users page
    When I click on the "Delete" action of the row which contains "Julia"
    Then I should see a confirm dialog with the following content:
      | title   | Confirm deletion                           |
      | content | Are you sure you want to delete this user? |
    When I confirm the deletion
    Then I should not see the text "Julia"

  Scenario: Successfully delete a user from user page
    Given I am on the users page
    When I click on the "Update" action of the row which contains "Julien"
    Then I press the secondary action "Delete"
    Then I should see a confirm dialog with the following content:
      | title   | Confirm deletion                           |
      | content | Are you sure you want to delete this user? |
    When I confirm the deletion
    Then I should not see the text "Julien"
