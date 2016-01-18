@javascript
Feature: Display last operations widget
  In order to have a quick overview of last import and export operations
  As a regular user
  I need to be able to see a last operations widget on the dashboard

  @unstable
  Scenario: Display last operations widget
    Given a "footwear" catalog configuration
    And the following job "footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.csv |
    And I am logged in as "Mary"
    When I am on the dashboard page
    Then I should see "Last operations"
    When I wait for widgets to load
    Then I should see "No operations found"
    When I am on the "footwear_category_export" export job page
    And I launch the export job
    And I wait for the "footwear_category_export" job to finish
    When I am on the dashboard page
    Then I should see "Last operations"
    When I wait for widgets to load
    Then I should see "Export Footwear category export Completed"

  Scenario: Show last operations list
    Given a "footwear" catalog configuration
    And the following job "footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.csv |
    And I am logged in as "Mary"
    When I am on the "footwear_category_export" export job page
    And I launch the export job
    And I wait for the "footwear_category_export" job to finish
    When I am on the dashboard page
    Then I should see "Last operations"
    When I click on the job tracker button on the job widget
    Then I should be redirected on the job tracker page
    And I should see the "Refresh" button
    And I should see the "Reset" button
    And I should see the columns Type, Job, User, Status and Started at
