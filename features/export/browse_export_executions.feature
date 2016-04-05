@javascript
Feature: Browse export executions
  In order to view the list of export executions that have been launched
  As a product manager
  I need to be able to view a list of them

  Scenario: Successfully view, sort and filter export executions
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the exports page
    And I launch the "csv_footwear_product_export" export job
    And I launch the "csv_footwear_category_import" export job
    And I launch the "csv_footwear_attribute_export" export job
    And I launch the "csv_footwear_product_export" export job
    Then I am on the export executions page
    And the grid should contain 4 elements
    And I should see the columns Code, Label, Job, Date and Status
    And I should see entities csv_footwear_product_export, csv_footwear_attribute_export, csv_footwear_category_export and csv_footwear_product_export
    And the rows should be sorted descending by Date
    And I should be able to sort the rows by Code, Label, Job, Date and Status
    And I should be able to use the following filters:
      | filter | value                   | result                                                   |
      | Code   | product                 | csv_footwear_product_export, csv_footwear_product_export |
      | Label  | category                | csv_footwear_category_export                             |
      | Job    | Attribute export in CSV | csv_footwear_attribute_export                            |
      | Status | STOPPING                |                                                          |
