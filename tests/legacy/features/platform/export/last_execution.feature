@javascript
Feature: Display only logged user's jobs execution in last executions job view
  In order to have an overview of last job executions
  As a regular user
  I need to be able to browse the last job executions

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the exports grid
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    And I logout
    And I am logged in as "admin"
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish

  Scenario: Only view last executions of user
    Given I am on the exports grid
    When I click on the "CSV footwear product export" row
    Then the grid should contain 1 element
