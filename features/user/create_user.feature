@javascript
Feature: Create a user
  In order to manage the users and rights
  As Peter
  I need to be able to create a user

  Background:
    Given I am logged in as "admin"

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
    And I select the role "Role_user"
    When I save the user
    Then I should see "Doe, Jack"
    And I should see "User successfully saved"

