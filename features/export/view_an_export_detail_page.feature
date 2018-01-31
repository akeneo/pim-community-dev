@javascript
Feature: View an export detail page
  In order to know if an export is ready to be executed
  As a product manager
  I need to have access to a show export page which will present me its status

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully display the export information
    Given I am on the exports grid
    When I click on the "CSV footwear product export" row
    Then I should be on the "csv_footwear_product_export" export job page
    And I should see the text "Export profile - CSV footwear product export"
