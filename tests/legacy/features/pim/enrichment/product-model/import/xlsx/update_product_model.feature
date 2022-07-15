@javascript
Feature: Update product models through XLSX import
  In order to setup my application
  As a product manager
  I need to be able to update existing product models

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Julia successfully updates an exiting product sub product model through XLSX import
    Given the following root product model:
      | code     | parent   | family_variant      | categories         | collection | description-en_US-ecommerce | erp_name-en_US | price   |
      | code-001 |          | clothing_color_size | master_men         | Spring2017 | description                 | Blazers_1654   | 100 EUR |
    And the following sub product model:
      | code     | parent   | family_variant      | categories         | color | variation_name-en_US | composition |
      | code-002 | code-001 | clothing_color_size | master_men_blazers | blue  | Blazers              | composition |
    And the following XLSX file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-002;code-001;clothing_color_size;master_men_blazers;;A new description for a sub model;;;blue;Beautiful blazers;composition;;;;
      """
    And the following job "xlsx_catalog_modeling_product_model_import" configuration:
      | filePath          | %file to import% |
      | enabledComparison | no               |
    When I am on the "xlsx_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "xlsx_catalog_modeling_product_model_import" job to finish
    Then there should be the following root product model:
      | code     | categories | family_variant      | collection   | description-en_US-ecommerce | erp_name-en_US | price      |
      | code-001 | master_men | clothing_color_size | [Spring2017] | description                 | Blazers_1654   | 100.00 EUR |
    And there should be the following product model:
      | code     | color  | variation_name-en_US | composition |
      | code-002 | [blue] | Beautiful blazers    | composition |
