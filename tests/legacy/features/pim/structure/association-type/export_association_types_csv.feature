@javascript
Feature: Export association types
  In order to be able to access and modify association types data outside PIM
  As a product manager
  I need to be able to export association types

  @critical
  Scenario: Successfully export association types
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_association_type_export" configuration:
      | filePath | %tmp%/association_type_export/association_type_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_association_type_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_association_type_export" job to finish
    Then file "%tmp%/association_type_export/association_type_export.csv" should contain 5 rows
