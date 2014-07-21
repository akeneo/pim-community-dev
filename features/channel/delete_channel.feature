@javascript
Feature: Delete a channel
  In order to manage channels for the catalog
  As an administrator
  I need to be able to delete channels

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully delete a channel from the grid
    Given I am on the channels page
    And I should see channels tablet and mobile
    When I click on the "Delete" action of the row which contains "tablet"
    And I confirm the deletion
    Then I should see flash message "Channel successfully removed"
    And the grid should contain 1 element
    And I should not see channel tablet

  Scenario: Successfully delete a channel
    Given I edit the "mobile" channel
    When I press the "Delete" button
    And I confirm the deletion
    Then I should see flash message "Channel successfully removed"
    And the grid should contain 1 element
    And I should not see channel mobile
