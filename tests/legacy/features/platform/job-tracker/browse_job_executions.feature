@javascript
Feature: Display jobs execution in job tracker
  In order to have an overview of last job operations
  As a regular user
  I need to be able to browse the job executions in the job tracker

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "csv_footwear_category_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_category_export" job to finish
    And I am on the job tracker page

  Scenario: Successfully search on label
    When I search "footwear category export"
    Then the grid should contain 1 element
    And I should see entity CSV footwear category export

  Scenario: Successfully view only my jobs
    Given I logout
    And I am logged in as "admin"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources View all jobs in process tracker
    And I save the role
    And I should not see the text "There are unsaved changes."
    When I am on the job tracker page
    Then the grid should contain 0 element
