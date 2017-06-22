@javascript
Feature: Export associations in XLSX
  In order to be able to access and modify association types data outside PIM
  As a product manager
  I need to be able to export association types in XLSX

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export association types
    Given the following job "xlsx_footwear_association_type_export" configuration:
      | filePath | %tmp%/association_type_export/association_type_export.xlsx |
    And I am on the "xlsx_footwear_association_type_export" export job page
    When I launch the export job
    And I wait for the "xlsx_footwear_association_type_export" job to finish
    Then xlsx file "%tmp%/association_type_export/association_type_export.xlsx" should contain 5 rows

  Scenario: Successfully export associations into several files
    Given the following job "xlsx_footwear_association_type_export" configuration:
      | filePath     | %tmp%/xlsx_footwear_association_type_export/xlsx_footwear_association_type_export.xlsx |
      | linesPerFile | 3                                                                                      |
    When I am on the "xlsx_footwear_association_type_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_association_type_export" job to finish
    Then I should see the secondary action "xlsx_footwear_association_type_export_1.xlsx"
    And I should see the secondary action "xlsx_footwear_association_type_export_2.xlsx"
    And exported xlsx file 1 of "xlsx_footwear_association_type_export" should contain:
      | code         | label-en_US  |
      | X_SELL       | Cross sell   |
      | UPSELL       | Upsell       |
      | SUBSTITUTION | Substitution |
    And exported xlsx file 2 of "xlsx_footwear_association_type_export" should contain:
      | code | label-en_US |
      | PACK | Pack        |
