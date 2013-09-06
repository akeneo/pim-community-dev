@javascript
Feature: Edit an user
  In order to manage the users and rights
  As Peter
  I need to be able to edit an user

  Background:
    Given I am logged in as "admin"

  Scenario: Successfully edit an user
    Given I edit the "admin" user
    Then I should see "Doe, John"
    Then I fill in the following information:
      | Last name | Smith |
    When I save the user
    Then I should see "Smith, John"
    And I should see "User successfully saved"

