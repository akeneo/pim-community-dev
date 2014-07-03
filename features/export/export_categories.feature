Feature: Export categories
  In order to be able to access and modify category data outside PIM
  As a product manager
  I need to be able to import and export categories

  @javascript
  Scenario: Successfully export categories
    Given a "footwear" catalog configuration
    And the following job "footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_category_export" export job page
    When I launch the export job
    And I wait for the "footwear_category_export" job to finish
    Then file "%tmp%/category_export/category_export.csv" should contain 6 rows
    And the category order in the file "%tmp%/category_export/category_export.csv" should be following:
      | 2014_collection   |
      | summer_collection |
      | winter_collection |
      | winter_boots      |
      | sandals           |
