@javascript
Feature: Handle import of invalid CSV data
  In order to ease the correction of an invalid CSV file import
  As a product manager
  I need to be able to download a CSV file containing all invalid data of an import

  Background:
    Given the "footwear" catalog configuration

  Scenario: From a product CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      sku;family
      SKU-001;NO_FAMILY
      SKU-002;sneakers
      SKU-003;sneakers
      SKU-004;sneakers
      SKU-005;boots
      SKU-006;boots
      SKU-007;sneakers
      SKU-008;OTHER_FAMILY
      SKU-009;sneakers
      SKU-010;boots
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_product_import" export job page
    And I launch the "csv_footwear_product_import" import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see "Download invalid data" on the "Download generated files" dropdown button
    And the invalid data file of "csv_footwear_product_import" should contain:
      """
      sku;family
      SKU-001;NO_FAMILY
      SKU-008;OTHER_FAMILY
      """
