@javascript
Feature: Display jobs execution in job tracker
  In order to have an overview of last job operations
  As a regular user
  I need to be able to see a last operations on the job tracker

  Background:
    Given a "footwear" catalog configuration

  Scenario: Display an export in the job tracker
    Given I am logged in as "Julia"
    And the following job "csv_footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.csv |
    When I am on the "csv_footwear_category_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_category_export" job to finish
    When I am on the job tracker page
    And I should see the columns Job, Type, Started at, Status and Warnings
    And the grid should contain 1 element
    And I should see entity CSV footwear category export

  Scenario: Display a mass edit in the job tracker
    Given I am logged in as "Julia"
    And the following products:
      | sku      | family   |
      | boots    | boots    |
      | sneakers | sneakers |
      | sandals  | sandals  |
    When I am on the products grid
    Then I select rows Boots, Sandals and Sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    When I am on the dashboard page
    When I am on the job tracker page
    And I should see the columns Job, Type, Started at, Status and Warnings
    And the grid should contain 1 element
    And I should see entity Mass edit common product attributes

  Scenario: Display an import in the job tracker
    Given I am logged in as "Julia"
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
    And I should see the columns Job, Type, Started at, Status and Warnings
    And the grid should contain 1 element
    And I should see entity CSV footwear category import

  Scenario: Successfully deny to view progress when the user do not have export show access
    Given I am logged in as "admin"
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    And I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Show an export profile
    And I save the role
    And I should not see the text "There are unsaved changes"
    And I logout
    And I am logged in as "Julia"
    And I am on the job tracker page
    And the grid should contain 0 element
