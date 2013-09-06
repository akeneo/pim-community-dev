@javascript
Feature: Edit an user
  In order to manage the users and rights
  As Peter
  I need to be able to create an user

  Background:
    Given I am logged in as "admin"

  Scenario: Successfully create an user
    Given I am on the user creation page
    Then I fill in the following information:
      | Username   | julia |
      | First name | Julia |
      | Last name  | Doe   |
    When I save the user
    Then I should see "Doe, Julia"
    And I should see "User successfully saved"

