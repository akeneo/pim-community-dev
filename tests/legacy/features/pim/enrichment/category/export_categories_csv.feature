@javascript
Feature: Export categories
  In order to be able to access and modify category data outside PIM
  As a product manager
  I need to be able to import and export categories

  @critical
  Scenario: Successfully export categories in CSV
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_category_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_category_export" job to finish
    Then file "%tmp%/category_export/category_export.csv" should contain 6 rows
    And the category order in the file "%tmp%/category_export/category_export.csv" should be following:
      | 2014_collection   |
      | summer_collection |
      | sandals           |
      | winter_collection |
      | winter_boots      |
