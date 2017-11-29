@javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products with associations

  Background:
    Given the "catalog_modeling" catalog configuration
    And the following product groups:
      | code  | label-en_US | type    |
      | CROSS | Bag Cross   | RELATED |
    And I am logged in as "Julia"
  Scenario: Successfully import a csv file of products with product model associations
    Given the following CSV file to import:
      """
      sku;family;X_SELL-productmodels;UPSELL-productmodels
      SKU-001;clothing;amor,brooksblue;
      1111111171;accessories;;brookspink
      """
    And the following job "csv_catalog_modeling_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_import" job to finish
    Then the product "SKU-001" should have the following associations:
      | type   | productmodels   |
      | X_SELL | amor,brooksblue |
    And the product "1111111171" should have the following associations:
      | type   | productmodels   |
      | UPSELL | brookspink      |
