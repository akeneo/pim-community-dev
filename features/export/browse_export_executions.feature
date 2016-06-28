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
    And I launch the "csv_footwear_category_export" export job
    And I launch the "csv_footwear_attribute_export" export job
    And I launch the "csv_footwear_product_export" export job
    Then I am on the export executions page
    And the grid should contain 4 elements
    And I should see the columns Code, Label, Job, Date and Status
    And I should see entities csv_footwear_product_export, csv_footwear_attribute_export, csv_footwear_category_export and csv_footwear_product_export
    And the rows should be sorted descending by Date
    And I should be able to sort the rows by Code, Label, Job, Date and Status
    And I should be able to use the following filters:
      | filter    | operator  | value                                       | result                                                                                                |
      | code      | contains  | product                                     | csv_footwear_product_export, csv_footwear_product_export                                              |
      | label     | contains  | category                                    | csv_footwear_category_export                                                                          |
      | job_name  |           | Attribute export in CSV                     | csv_footwear_attribute_export                                                                         |
      | status    |           | STOPPING                                    |                                                                                                       |
      | date      | more than | 09/01/2015 05:00 PM                         | footwear_product_export, footwear_category_export, footwear_attribute_export, footwear_product_export |
      | date      | between   | 09/01/2050 05:00 PM and 09/01/2100 05:00 AM |                                                                                                       |
