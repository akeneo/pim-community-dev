Feature: Skip invalid product models through CSV
  In order to import correct product model
  As a catalog manager
  I need to be able to skip imported product models with invalid data

  Background:
    Given the "catalog_modeling" catalog configuration

  Scenario: Skip a root product model if a code and a family variant are not defined
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-001;;;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      ;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      """
    When I import it via the job "csv_catalog_modeling_product_model_import" as "Julia"
    And I wait for this job to finish
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

  Scenario: Skip a product model if a code or a parent are not defined
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-001;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      ;code-001;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
    When I import it via the job "csv_catalog_modeling_product_model_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "created 1"
    And I should see the text "skipped 1"
    And I should see the text "The product model code must not be empty"
    And the invalid data file of "csv_catalog_modeling_product_model_import" should contain:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      ;code-001;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """

  Scenario: Skip a product model if the parent does not exist or is not a root product model
    Given the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-003;code-002;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      code-004;code-005;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
    When I import it via the job "csv_catalog_modeling_product_model_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "The product model \"code-003\" cannot have the product model \"code-002\" as parent"
    And I should see the text "Property \"parent\" expects a valid parent code. The product model does not exist, \"code-005\" given"
    And the invalid data file of "csv_catalog_modeling_product_model_import" should contain:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-003;code-002;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      code-004;code-005;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """

  Scenario: A root product model cannot have a parent
    Given the following root product models:
      | code     | parent | family_variant      | categories | collection | description-en_US-ecommerce | erp_name-en_US | price   |
      | code-001 |        | clothing_color_size | master_men | Spring2017 | A description for 001       | Blazers_1654   | 100 EUR |
      | code-002 |        | clothing_color_size | master_men | Spring2017 | A description for 002       | Blazers_1654   | 50 EUR  |
    And the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;ean;sku;weight
      code-002;code-001;clothing_colorsize;master_men;Spring2017;A description for 002;Blazers_1654;50 EUR;;;;;;;
      """
    When I import it via the job "csv_catalog_modeling_product_model_import" as "Julia"
    And I wait for this job to finish
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "parent: Property \"parent\" cannot be modified, \"code-001\" given."
    And the invalid data file of "csv_catalog_modeling_product_model_import" should contain:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;name-en_US;composition;size;ean;sku;weight
      code-002;code-001;clothing_colorsize;master_men;Spring2017;A description for 002;Blazers_1654;50 EUR;;;;;;;
      """

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
    When I import it via the job "csv_catalog_modeling_product_model_import" as "Julia"
    And I wait for this job to finish
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
    When I import it via the job "csv_catalog_modeling_product_model_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "Cannot set value \"[blue]\" for the attribute axis \"color\", as another sibling entity already has this value"
    And the invalid data file of "csv_catalog_modeling_product_model_import" should contain:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-003;code-001;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      """
