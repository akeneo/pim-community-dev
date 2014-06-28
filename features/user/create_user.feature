Feature: Create a user
  In order to manage the users and rights
  As an administrator
  I need to be able to create a user

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  @javascript
  Scenario: Successfully create a user
    Given I am on the user creation page
    And I fill in the following information:
      | Username          | jack     |
      | First name        | Jack     |
      | Last name         | Doe      |
      | Password          | DoeDoe   |
      | Re-enter password | DoeDoe   |
      | Status            | Inactive |
      | Owner             | Main     |
    And I scroll down
    And I fill in the following information:
      | E-mail | jack@example.com |
    And I visit the "Groups and Roles" tab
    And I select the role "User"
    When I save the user
    Then there should be a "jack" user
