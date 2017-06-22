@javascript
Feature: Browse export executions
  In order to view the list of export executions that have been launched
  As a product manager
  I need to be able to view a list of them

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the exports page
    And I am on the "csv_footwear_product_export" export job page
    And I launch the "csv_footwear_product_export" export job
    And I am on the "csv_footwear_category_export" export job page
    And I launch the "csv_footwear_category_export" export job
    And I am on the "csv_footwear_attribute_export" export job page
    And I launch the "csv_footwear_attribute_export" export job
    And I am on the "csv_footwear_product_export" export job page
    And I launch the "csv_footwear_product_export" export job
    Then I am on the export executions page
    And the grid should contain 4 elements
    And I should see the columns Code, Label, Job, Date, Status and Warnings
    And I should see entities csv_footwear_product_export, csv_footwear_attribute_export, csv_footwear_category_export and csv_footwear_product_export
    And the rows should be sorted descending by Date

  Scenario: Successfully view and sort export executions
    Then I should be able to sort the rows by Code, Label, Job, Date and Status

  Scenario Outline: Successfully filter export executions
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter   | operator  | value                                       | result                                                                                                | count |
      | code     | contains  | product                                     | csv_footwear_product_export, csv_footwear_product_export                                              | 2     |
      | job_name |           | Attribute export in CSV                     | csv_footwear_attribute_export                                                                         | 1     |
      | status   |           | Stopping                                    |                                                                                                       | 0     |
      | date     | more than | 09/01/2015 05:00 PM                         | footwear_product_export, footwear_category_export, footwear_attribute_export, footwear_product_export | 4     |
      | date     | between   | 09/01/2050 05:00 PM and 09/01/2100 05:00 AM |                                                                                                       | 0     |

  Scenario: Successfully search on label
    When I search "category"
    Then the grid should contain 1 element
    And I should see entity csv_footwear_category_export
