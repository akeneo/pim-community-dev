Feature: Create and delete a user role
  In order to manage the users and rights
  As an administrator
  I need to be able to create, edit and delete user roles

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  @javascript
  Scenario: Successfully create, edit and delete a user role
    Given I am on the user roles creation page
    And I fill in the following information:
      | Role | Dummy role |
    When I save the role
    And I should see the text "Dummy role"
    When I edit the "Dummy role" user role
    And I fill in the following information:
      | Role | VeryDummyRole |
    When I save the role
    Then I should see the text "VeryDummyRole"
    Given I am on the user roles page
    When I click on the "Delete" action of the row which contains "VeryDummyRole"
    Then I should see a confirm dialog with the following content:
      | title   | Delete Confirmation                        |
      | content | Are you sure you want to delete this role? |
    When I confirm the deletion
    Then I should not see "VeryDummyRole"
    When I click on the "Delete" action of the row which contains "Administrator"
    And I cancel the deletion
    And I should see the text "Administrator"

  @javascript
  Scenario: Successfully display validation errors when creating or editing a user role
    Given I am on the user roles creation page
    When I save the role
    Then I should see validation tooltip "This value should not be blank."
    When I edit the "Administrator" user role
    And I fill in the following information:
      | Role |  |
    When I save the role
    Then I should see validation tooltip "This value should not be blank."
