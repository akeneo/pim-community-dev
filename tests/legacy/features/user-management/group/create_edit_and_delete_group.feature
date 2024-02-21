@javascript
Feature: Create a group
  In order to manage the users and rights
  As an administrator
  I need to be able to create, edit and delete user groups

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully create and delete a user group
    Given I am on the user groups creation page
    And I fill in the following information:
      | Name | DummyGroup |
    When I save the group
    Then there should be a "DummyGroup" user group
    When I am on the user groups page
    Then the grid should contain 4 elements
    And I should see the text "DummyGroup"
    When I am on the user groups page
    Then the grid should contain 4 elements
    And I should see the text "DummyGroup"
    When I click on the "Delete" action of the row which contains "DummyGroup"
    Then I should see a confirm dialog with the following content:
      | title   | Confirm deletion                            |
      | content | Are you sure you want to delete this group? |
    And I confirm the deletion
    Then I should not see the text "DummyGroup"
    And the grid should contain 3 elements
