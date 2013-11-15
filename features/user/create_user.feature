Feature: Create a user
  In order to manage the users and rights
  As Peter
  I need to be able to create a user

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  @javascript
  Scenario: Successfully create a user
    Given I am on the user creation page
    Then I fill in the following information:
      | Username          | jack             |
      | First name        | Jack             |
      | Last name         | Doe              |
      | Password          | DoeDoe           |
      | Re-enter password | DoeDoe           |
      | E-mail            | jack@example.com |
    And I select the status "Inactive"
    And I select the owner "Main"
    And I select the role "User"
    When I save the user
    Then there should be a "jack" user
