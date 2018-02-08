Feature: Create product models through XLSX import
  In order to setup my application
  As a product manager
  I need to be able to import new product model

  Background:
    Given the "catalog_modeling" catalog configuration

  Scenario: Julia imports new root products models in XLSX
    Given the following XLSX file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;ean;sku;weight
      code-001;;clothing_colorsize;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      """
    When I import it via the job "xlsx_catalog_modeling_product_model_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following root product model:
      | code     | categories | family_variant     | collection   | description-en_US-ecommerce | erp_name-en_US | price      |
      | code-001 | master_men | clothing_colorsize | [Spring2017] | description                 | Blazers_1654   | 100.00 EUR |

  Scenario: Julia imports new products sub product models in XLSX
    Given the following XLSX file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-001;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      code-002;code-001;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
    When I import it via the job "xlsx_catalog_modeling_product_model_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following root product model:
      | code     | categories | family_variant      | collection   | description-en_US-ecommerce | erp_name-en_US | price      |
      | code-001 | master_men | clothing_color_size | [Spring2017] | description                 | Blazers_1654   | 100.00 EUR |
    And there should be the following product model:
      | code     | color  | variation_name-en_US | composition |
      | code-002 | [blue] | Blazers              | composition |
