@javascript
Feature: Skip invalid product models through CSV
  In order to import correct product model
  As a catalog manager
  I need to be able to skip imported product models with invalid data

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Skip a root product model if a code and a family variant are not defined
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-001;;;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      ;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 2"
    And I should see the text "The product model code must not be empty"
    And I should see the text "Property \"family_variant\" expects a valid family variant code. The family variant does not exist, \"\" given"
    And the invalid data file of "csv_catalog_modeling_product_model_import" should contain:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-001;;;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      ;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      """

  @critical
  Scenario: The variant axis values of a product model are immutable
    Given the following root product model:
      | code     | parent   | family_variant      | categories         | collection | description-en_US-ecommerce | erp_name-en_US | price   |
      | code-001 |          | clothing_color_size | master_men         | Spring2017 | description                 | Blazers_1654   | 100 EUR |
    And the following sub product model:
      | code     | parent   | family_variant      | categories         | color | variation_name-en_US | composition |
      | code-002 | code-001 | clothing_color_size | master_men_blazers | blue  | Blazers              | composition |
    And the following CSV file to import:
      """
      code;parent;family_variant;color
      code-002;code-001;clothing_color_size;red
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath          | %file to import% |
      | enabledComparison | no               |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "Variant axis \"color\" cannot be modified, \"[red]\" given"
    And the invalid data file of "csv_catalog_modeling_product_model_import" should contain:
      """
      code;parent;family_variant;color
      code-002;code-001;clothing_color_size;red
      """

  Scenario: Skip a product model if its combination of axes values exist more than once in an import file
    Given the following root product model:
      | code     | parent   | family_variant      | categories         | collection | description-en_US-ecommerce | erp_name-en_US | price   |
      | code-001 |          | clothing_color_size | master_men         | Spring2017 | description                 | Blazers_1654   | 100 EUR |
    And the following sub product model:
      | code     | parent   | family_variant      | categories         | color | variation_name-en_US | composition |
      | code-002 | code-001 | clothing_color_size | master_men_blazers | blue  | Blazers              | composition |
    And the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-003;code-001;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "Cannot set value \"[blue]\" for the attribute axis \"color\" on product model \"code-003\", as the product model \"code-002\" already has this value"
    And the invalid data file of "csv_catalog_modeling_product_model_import" should contain:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-003;code-001;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
