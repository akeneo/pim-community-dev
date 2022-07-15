@javascript
Feature: Export categories in XLSX
  In order to be able to access and modify attributes data outside PIM
  As a product manager
  I need to be able to export categories in XLSX

  Background:
    Given an "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export categories
    Given the following job "xlsx_footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.xlsx |
    And I am on the "xlsx_footwear_category_export" export job page
    When I launch the export job
    And I wait for the "xlsx_footwear_category_export" job to finish
    Then xlsx file "%tmp%/category_export/category_export.xlsx" should contain 6 rows
    And the category order in the xlsx file "%tmp%/category_export/category_export.xlsx" should be following:
      | 2014_collection   |
      | summer_collection |
      | sandals           |
      | winter_collection |
      | winter_boots      |
