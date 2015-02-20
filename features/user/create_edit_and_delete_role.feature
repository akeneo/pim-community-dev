Feature: Create and delete a user role
  In order to manage the users and rights
  As an administrator
  I need to be able to create a role

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  @javascript
  Scenario: Successfully create a user role
    Given I am on the user roles creation page
    And I fill in the following information:
      | Role     | Dummy role |
    When I save the role
    Then the grid should contain 4 elements
    And I should see "Dummy role"

    Given I am on the user roles creation page
    When I save the role
    Then I should see validation tooltip "This value should not be blank."


  @javascript
  Scenario: Successfully edit a user role
    Given I am on the user roles page
    And I should see "Dummy role"
    When I click on the "Update" action of the row which contains "Dummy role"
    Given I edit the "Dummy role" user role
    And I fill in the following information:
      | Role     |  |
    When I save the role
    Then I should see validation tooltip "This value should not be blank."
    When I fill in the following information:
      | Role     | DummyRole2 |
    When I save the role
    Then I should see "DummyRole2"
    
  @javascript
  Scenario: Successfully delete a role after confirmation
    Given I am on the user roles page
    When I click on the "Delete" action of the row which contains "DummyRole2"
    Then I should see a confirm dialog with the following content:
      | title   | Delete Confirmation                        |
      | content | Are you sure you want to delete this role? |
    When I confirm the deletion
    Then I should not see "DummyRole2"

    When I click on the "Delete" action of the row which contains "Administrator"
    And I cancel the deletion
    And I should see "Administrator"
