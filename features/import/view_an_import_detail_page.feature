@javascript
Feature: View an import detail page
  In order to know if an import is ready to be executed
  As a product manager
  I need to have access to a show import page which will present me its status

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully display the import information
    Given I am on the imports page
    When I click on the "CSV footwear product import" row
    Then I should be on the "csv_footwear_product_import" import job page
    And I should see the text "Import profile - CSV footwear product"
