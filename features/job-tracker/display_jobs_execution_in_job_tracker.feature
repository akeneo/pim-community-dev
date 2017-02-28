@javascript
Feature: Display jobs execution in job tracker
  In order to have an overview of last job operations
  As a regular user
  I need to be able to see a last operations on the job tracker

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Display an export in the job tracker
    And the following job "csv_footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.csv |
    When I am on the "csv_footwear_category_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_category_export" job to finish
    When I am on the job tracker page
    Then I should see the "Refresh" button
    And I should see the "Reset" button
    And I should see the columns Type, Job, User, Started at, Status and Warnings
    And the grid should contain 1 element
    And I should see entity CSV footwear category export

  Scenario: Display a mass edit in the job tracker
    And the following products:
      | sku      | family   |
      | boots    | boots    |
      | sneakers | sneakers |
      | sandals  | sandals  |
    When I am on the products page
    Then I select rows boots, sandals and sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    When I am on the dashboard page
    When I am on the job tracker page
    Then I should see the "Refresh" button
    And I should see the "Reset" button
    And I should see the columns Type, Job, User, Started at, Status and Warnings
    And the grid should contain 1 element
    And I should see entity Mass edit common product attributes

  Scenario: Display an import in the job tracker
    And the following CSV file to import:
    """
    code;parent;label-en_US
    default;;
    computers;;Computers
    laptops;computers;Laptops
    hard_drives;laptops;Hard drives
    pc;computers;PC
    """
    And the following job "csv_footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_category_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_category_import" job to finish
    And I am on the job tracker page
    And I should see the "Refresh" button
    And I should see the "Reset" button
    And I should see the columns Type, Job, User, Started at, Status and Warnings
    And the grid should contain 1 element
    And I should see entity CSV footwear category import

  @jira https://akeneo.atlassian.net/browse/PIM-6140
  Scenario: Successfully filter job executions with "equals to" filter
    Given I am on the exports page
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    And I logout
    And I am logged in as "admin"
    And I am on the "csv_footwear_category_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_category_export" job to finish
    When I am on the job tracker page
    Then I should be able to use the following filters:
      | filter | operator    | value                       | result                                                    |
      | job    | is equal to | CSV footwear product export | CSV footwear product export                               |
      | user   | is equal to | Julia                       | CSV footwear product export                               |
      | type   | is equal to | import                      |                                                           |
      | type   | is equal to | export                      | CSV footwear product export, CSV footwear category export |

  Scenario: Successfully deny to view progress when the user do not have export show access
    Given I am logged in as "admin"
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    And I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I visit the "Export profiles" group
    And I revoke rights to resource Show an export profile
    And I save the role
    Then I should not see the text "There are unsaved changes"
    And I logout
    And I am logged in as "Julia"
    And I am on the job tracker page
    And I click on the "CSV footwear product export" row
    Then I should see the flash message "You do not have permission to this action"

