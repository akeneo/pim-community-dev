@javascript
Feature: Display notifications for import and export jobs
  In order to know when the import or export jobs I launched have finished
  As a product manager
  I need to see notifications for completed import and export jobs

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully display a notification when a job is finished
    Given I am on the "attribute_export" export job page
    And I launch the export job
    And I wait for the "attribute_export" job to finish
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                          |
      | success | Export Attribute export finished |
