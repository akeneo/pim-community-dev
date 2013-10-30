Feature: Edit a user
  In order to manage the users and rights
  As Peter
  I need to be able to edit a user

  Background:
    Given I am logged in as "admin"

  Scenario: Successfully edit a user
    Given I edit the "admin" user
    When I fill in the following information:
      | First name | John  |
      | Last name  | Smith |
    And I select "Main" from "Owner"
    And I save the user
    Then I should see "John Smith"
