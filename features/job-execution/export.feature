@javascript
Feature: Export profiles
  In order to easily export profiles
  As a product manager
  I need to be able to see the result of an export and to download logs, files and archives

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Go to the job execution page for an "export" and check buttons status on the header and "Show profile" button redirection
    Given the following job "xlsx_footwear_association_type_export" configuration:
      | filePath     | %tmp%/xlsx_footwear_association_type_export/xlsx_footwear_association_type_export.xlsx |
      | linesPerFile | 3                                                                                      |
    When I am on the "xlsx_footwear_association_type_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_association_type_export" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - XLSX footwear association type export [xlsx_footwear_association_type_export]"
    And I should see the text "Download log"
    And I should not see the text "Download read files"
    And I should see the text "Download generated file"
    And I should see the text "Download generated archive"
    And I should see the text "Show profile"
    When I press the "Show profile" button
    Then I should be redirected on the export page of "xlsx_footwear_association_type_export"
