@javascript
Feature: Execute a job
  In order to launch an export
  As a product manager
  I need to be able to execute a valid export

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Fail to launch a job with validation errors
    Given the following job "csv_footwear_product_export" configuration:
      | filePath |  |
    And I am on the "csv_footwear_product_export" export job page
    When I launch the "csv_footwear_product_export" export job
    Then I should not see the text "The export is running."
    And I should not see the text "An error occurred during the export execution."
