Feature: Export multiple XLSX files
  In order to be able to access and modify attributes large data outside PIM
  As a product manager
  I need to be able to export data into multiple XLSX files

  Background:
    Given an "footwear" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully export families into several files
    Given the following job "xlsx_footwear_family_export" configuration:
      | filePath     | %tmp%/foo/bar.xlsx |
      | linesPerFile | 2                  |
    When I am on the "xlsx_footwear_family_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_family_export" job to finish
    And I should see the text "Copy generated file(s) in the configured folder"
    And I should see the text "copied 3"
    And I press the "Download generated files" button
    Then I should see the text "bar1.xlsx"
    And I should see the text "bar2.xlsx"
    And I should see the text "bar3.xlsx"
