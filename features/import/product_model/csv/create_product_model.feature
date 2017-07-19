@javascript
Feature: Create product through CSV import
  In order to setup my application
  As a product manager
  I need to be able to import new product models

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Julia imports new root products models
    Given the following CSV file to import:
      """
      identifier;parent;family_variant;categories;collection;description;erp_name;price;color;name;composition;size;EAN;sku;weight
      identifier-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then there should be the following root product model:
      | identifier     | collection | description | erp_name     | price   |
      | identifier-001 | Spring2017 | description | Blazers_1654 | 100 EUR |

  Scenario: Julia imports new products sub-models
    Given the following CSV file to import:
      """
      identifier;parent;family_variant;categories;collection;description;erp_name;price;color;name;composition;size;EAN;sku;weight
      identifier-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      identifier-002;identifier-001;variant_clothing_color_and_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then there should be the following root product model:
      | identifier     | collection | description | erp_name     | price   |
      | identifier-001 | Spring2017 | description | Blazers_1654 | 100 EUR |
    And there should be the following product model:
      | identifier     | color | name    | composition |
      | identifier-002 | blue  | Blazers | composition |
