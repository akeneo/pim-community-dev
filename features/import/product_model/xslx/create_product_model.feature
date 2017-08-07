@javascript
Feature: Create product through XLSX import
  In order to setup my application
  As a product manager
  I need to be able to import new product model

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Julia imports new root products models
    Given the following XLSX file to import:
      """
      identifier;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight
      identifier-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      """
    And the following job "xlsx_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "xlsx_catalog_modeling_product_model_import" job to finish
    Then there should be the following root product model:
      | identifier     | categories | family_variant                  | collection   | description-en_US-ecommerce | erp_name-en_US | price      |
      | identifier-001 | master_men | variant_clothing_color_and_size | [Spring2017] | description                 | Blazers_1654   | 100.00 EUR |

  Scenario: Julia imports new products sub-models
    Given the following XLSX file to import:
      """
      identifier;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight
      identifier-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      identifier-002;identifier-001;variant_clothing_color_and_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
    And the following job "xlsx_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "xlsx_catalog_modeling_product_model_import" job to finish
    Then there should be the following root product model:
      | identifier     | categories | family_variant                  | collection   | description-en_US-ecommerce | erp_name-en_US | price      |
      | identifier-001 | master_men | variant_clothing_color_and_size | [Spring2017] | description                 | Blazers_1654   | 100.00 EUR |
    And there should be the following product model:
      | identifier     | color  | name-en_US | composition |
      | identifier-002 | [blue] | Blazers    | composition |
