@javascript
Feature: Delete an association
  In order to manage associations in the catalog
  As a user
  I need to be able to delete associations

  Background:
    Given the following associations:
      | code       |
      | cross_sell |
      | up_sell    |
    And I am logged in as "admin"

  Scenario: Successfully delete an association from the grid
    Given I am on the associations page
    Then I should see association cross_sell
    When I click on the "Delete" action of the row which contains "cross_sell"
    And I confirm the deletion
    Then I should not see association cross_sell

  Scenario: Successfully delete a association from the edit page
    Given I edit the "up_sell" association
    When I press the "Delete" button
    And I confirm the deletion
    Then the grid should contain 1 element
    And I should not see association "up_sell"
