@javascript
Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Import product without optional attributes, they are ignored
    Given the following CSV file to import:
      """
      parent;family;categories;ean,sku,material
      code-001;clothing;master_men;ean,sku,metal
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then there should be the following root product model:
      | code     | categories | family_variant      | collection   | description-en_US-ecommerce | erp_name-en_US | price      |
      | code-001 | master_men | clothing_color_size | [Spring2017] | description                 | Blazers_1654   | 100.00 EUR |
    And there should be the following product model:
      | code     | color  | variation_name-en_US | composition |
      | code-002 | [blue] | Blazers              | composition |
