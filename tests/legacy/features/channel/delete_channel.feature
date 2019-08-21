@javascript
Feature: Delete a channel
  In order to manage channels for the catalog
  As an administrator
  I need to be able to delete channels

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully delete a channel
    Given I am on the "mobile" channel page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Channel successfully removed"
    And the grid should contain 1 element
    And I should not see channel Mobile
