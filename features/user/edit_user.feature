Feature: Edit a user
  In order to manage the users and rights
  As Peter
  I need to be able to edit a user

  Background:
    Given I am logged in as "admin"

  Scenario: Successfully edit a user
    Given I edit the "admin" user
    Then I should see "Doe, John"
    When I fill in the following information:
      | Last name | Smith |
    And I save the user
    Then I should see "Smith, John"
    And I should see "User successfully saved"
