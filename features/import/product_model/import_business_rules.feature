@javascript
Feature: Create product through CSV import
  In order to import product model
  As a catalog manager
  I need to be able to import product models with valid data

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Skip a root product model if a code and a family variant are not defined
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight
      code-001;;;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      ;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "skipped 2"
    And I should see the text "The product model code must not be empty"
    And I should see the text "Property \"family_variant\" expects a valid family variant code. The family variant does not exist, \"\" given"

  Scenario: Skip a product model if a code, a parent and a family variant are not defined
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight
      code-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      ;code-001;variant_clothing_color_and_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      code-003;code-001;;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "created 1"
    Then I should see the text "skipped 3"
    And I should see the text "The product model code must not be empty"
    And I should see the text "Property \"family_variant\" expects a valid family variant code. The family variant does not exist, \"\" given"

  Scenario: Skip a product model if the parent does not exist or it is not a root product model
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight
      code-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      code-002;code-001;variant_clothing_color_and_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      code-003;code-002;variant_clothing_color_and_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      code-004;code-005;variant_clothing_color_and_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "created 1"
    Then I should see the text "skipped 2"
    And I should see the text "Property \"family_variant\" expects a valid family variant code. The family variant does not exist, \"code-004\" given"
    And I should see the text "The sub product model parent must be a root product model"

  Scenario: Skip the products sub-model if variant axes are empty
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight
      code-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      code-002;code-001;variant_clothing_color_and_size;master_men_blazers;;;;;;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "created 1"
    Then I should see the text "skipped 1"
    And I should see the text "Attribute \"color\" cannot be empty, as it is defined as an axis for this entity: Pim\Component\Catalog\Model\ProductModel"

  Scenario: Only the attributes with values defined as "common attributes" in the variant of the family are updated.
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight
      code-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;blue;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then the product model "code-001" should not have the following values "composition, name-en_US, color"

  Scenario: Only the attributes with values defined as variant attributes level 1 in the variant of the family are updated.
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight
      code-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;blue;Blazers;composition;;;;
      code-002;code-001;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;blue;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then the product model "code-002" should not have the following values "collection, description-en_US-ecommerce, erp_name-en_US, price"

  Scenario: Skip the product model if some attributes with values are not defined in the family
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;EAN;sku;weight;EAN
      code-001;;variant_clothing_color_and_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;blue;Blazers;composition;;;;;EAN
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "skipped 1"
    And I should see the text "Attribute \"EAN\" does not belong to the family \"clothing\""