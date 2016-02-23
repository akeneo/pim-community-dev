@javascript
Feature: View an export detail page
  In order to know if an export is ready to be executed
  As a product manager
  I need to have access to a show export page which will present me its status

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully display the export information
    Given I am on the exports page
    When I click on the "footwear_product_export" row
    Then I should be on the "footwear_product_export" export job page
    And I should see the text "Export profile - Footwear product export"
