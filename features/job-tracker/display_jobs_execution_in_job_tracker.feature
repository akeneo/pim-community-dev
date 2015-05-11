Feature: Display jobs execution in job tracker
  In order to have an overview of last job operations
  As a regular user
  I need to be able to see a last operations on the job tracker

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Display an export in the job tracker
    And the following job "footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.csv |
    When I am on the "footwear_category_export" export job page
    And I launch the export job
    And I wait for the "footwear_category_export" job to finish
    When I am on the dashboard page
    When I click on the job tracker button on the job widget
    Then I should be redirected on the job tracker page
    And I should see the "Refresh" button
    And I should see the "Reset" button
    And I should see the columns Type, Job, User, Status and Started at
    And the grid should contain 1 element
    And I should see entity Footwear category export

  @javascript
  Scenario: Display a mass edit in the job tracker
    And the following products:
      | sku       | family     |
      | boots     | boots      |
      | sneakers  | sneakers   |
      | sandals   | sandals    |
    When I am on the products page
    Then I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    And I wait for the "edit_common_attributes" mass-edit job to finish
    When I am on the dashboard page
    When I click on the job tracker button on the job widget
    Then I should be redirected on the job tracker page
    And I should see the "Refresh" button
    And I should see the "Reset" button
    And I should see the columns Type, Job, User, Status and Started at
    And the grid should contain 1 element
    And I should see entity Mass edit common product attributes

  @javascript
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
    And the following job "footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_category_import" import job page
    And I launch the import job
    And I wait for the "footwear_category_import" job to finish
    When I am on the dashboard page
    When I click on the job tracker button on the job widget
    Then I should be redirected on the job tracker page
    And I should see the "Refresh" button
    And I should see the "Reset" button
    And I should see the columns Type, Job, User, Status and Started at
    And the grid should contain 1 element
    And I should see entity Footwear category import
