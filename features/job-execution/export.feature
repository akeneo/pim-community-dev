@javascript
Feature: Export profiles
  In order to easily export profiles
  As a product manager
  I need to be able to see the result of an export and to download logs, files and archives

  Background:
    Given a "footwear" catalog configuration
    And the following job "xlsx_footwear_association_type_export" configuration:
      | filePath     | %tmp%/xlsx_footwear_association_type_export/xlsx_footwear_association_type_export.xlsx |
      | linesPerFile | 3                                                                                      |
    And I am logged in as "Peter"

  Scenario: Go to the job execution page for an "export" and check buttons status on the header and "Show profile" button redirection
    Given I am on the "xlsx_footwear_association_type_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_association_type_export" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - XLSX footwear association type export [xlsx_footwear_association_type_export]"
    And I should see the secondary action "Download invalid data"
    And I should not see the secondary action "Download read files"
    And I press "xlsx_footwear_association_type_export_1.xlsx" on the "Download generated files" dropdown button
    And I press "xlsx_footwear_association_type_export_2.xlsx" on the "Download generated files" dropdown button
    And I should see the text "Download generated archive"
    And I should see the text "Show profile"
    When I press the "Show profile" button
    Then I should be redirected on the export page of "xlsx_footwear_association_type_export"

  Scenario: Go to the job execution page for an "export" without rights to download logs
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Download export report log
    And I save the role
    And I should not see the text "There are unsaved changes."
    When I am on the "xlsx_footwear_association_type_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_association_type_export" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - XLSX footwear association type export [xlsx_footwear_association_type_export]"
    And I should not see the secondary action "Download log"
    And I should not see the secondary action "Download read files"
    And I press "xlsx_footwear_association_type_export_1.xlsx" on the "Download generated files" dropdown button
    And I press "xlsx_footwear_association_type_export_2.xlsx" on the "Download generated files" dropdown button
    And I should see the text "Show profile"
    When I press the "Show profile" button
    Then I should be redirected on the export page of "xlsx_footwear_association_type_export"

  Scenario: Go to the job execution page for an "export" without rights to generated files
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Download exported files
    And I save the role
    And I should not see the text "There are unsaved changes."
    When I am on the "xlsx_footwear_association_type_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_association_type_export" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Execution details - XLSX footwear association type export [xlsx_footwear_association_type_export]"
    And I should see the secondary action "Download invalid data"
    And I should not see the secondary action "Download read files"
    And I should not see the secondary action "xlsx_footwear_association_type_export_1.xlsx"
    And I should not see the secondary action "xlsx_footwear_association_type_export_2.xlsx"
    And I should not see the secondary action "Download generated archive"
    And I should see the text "Show profile"
    When I press the "Show profile" button
    Then I should be redirected on the export page of "xlsx_footwear_association_type_export"
