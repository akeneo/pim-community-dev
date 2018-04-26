@javascript
Feature: Handle import of invalid data
  In order to correct an import job that failed
  As a product manager
  I need to be able to see the content of the items of the import that have invalid data

  Background:
    Given a "footwear" catalog configuration

  Scenario: Display items of a products import failed
    Given I am logged in as "Julia"
    And the following CSV file to import:
      """
      sku;family;handmade
      SKU-001;NO_FAMILY;1
      SKU-003;sneakers;0
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And I press the "Display item" button
    Then I should see the text "Warning"
    And I should see the text "Property \"family\" expects a valid family code. The family does not exist, \"NO_FAMILY\" given."
    And I should see the text "{\"sku\":[{\"locale\":null,\"scope\":null,\"data\":\"SKU-001\"}],\"handmade\":[{\"locale\":null,\"scope\":null,\"data\":true}]}"
