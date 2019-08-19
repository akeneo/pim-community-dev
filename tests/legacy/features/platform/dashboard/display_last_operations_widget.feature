@javascript
Feature: Display last operations widget
  In order to have a quick overview of last import and export operations
  As a regular user
  I need to be able to see a last operations widget on the dashboard

  Background:
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.csv |
    And I am logged in as "Mary"

  Scenario: Display last operations widget
    When I am on the dashboard page
    Then I should see the text "Last operations"
    Then I should see the text "No operations found"
    When I am on the "csv_footwear_category_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_category_export" job to finish
    When I am on the dashboard page
    Then I should see the text "Last operations"
    Then I should see the text "CSV footwear category export"

  Scenario: Show last operations list in the job tracker
    When I am on the "csv_footwear_category_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_category_export" job to finish
    When I am on the dashboard page
    Then I should see the text "Last operations"
    And I follow the link "Show job tracker"
    Then I should be redirected on the job tracker page
    And I should see the columns Job, Type, Started at, Status and Warnings
