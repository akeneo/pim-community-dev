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
    When the product models are imported via the job xlsx_catalog_modeling_product_model_import
    Then there should be the following root product model:
      | code     | categories | family_variant     | collection   | description-en_US-ecommerce | erp_name-en_US | price      |
      | code-001 | master_men | clothing_colorsize | [Spring2017] | description                 | Blazers_1654   | 100.00 EUR |
