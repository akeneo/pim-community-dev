@javascript
Feature: Display notifications for mass edit jobs
  In order to know when the mass edit jobs I launched have finished
  As a product manager
  I need to see notifications for completed mass edit jobs

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  @ce
  Scenario: Successfully display a notification when a mass-edit job is finished
    Given a disabled "boat" product
    And a disabled "jet-ski" product
    And I am on the products page
    When I mass-edit products boat and jet-ski
    And I choose the "Change status (enable / disable)" operation
    And I enable the products
    And I wait for the "change-status" mass-edit job to finish
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                 |
      | success | Mass edit Mass update products finished |
