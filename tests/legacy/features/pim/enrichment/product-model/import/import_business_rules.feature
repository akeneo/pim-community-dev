@javascript
Feature: Create product models through CSV import
  In order to import product model
  As a catalog manager
  I need to be able to import product models with valid data

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Skip a product model saving if its parent is the last product model in the tree (it should be a product variant instead).
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;sku;eu_shoes_size
      code-001;;shoes_size;master_men;;
      code-002;code-001;shoes_size;master_men;sku;42
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "created 1"
    And I should see the text "skipped 1"
    And I should see the text "The product model \"code-002\" cannot have a parent"

  Scenario: Skip the products sub product model if variant axes are empty
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-001;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      code-002;code-001;clothing_color_size;master_men_blazers;;;;;;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "created 1"
    Then I should see the text "skipped 1"
    And I should see the text "Attribute \"color\" cannot be empty, as it is defined as an axis for this entity"

  @critical
  Scenario: Only the attributes with values defined as "common attributes" in the family variant are updated.
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-001;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;blue;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then the product model "code-001" should not have the following values "composition, name-en_US, color"

  @critical
  Scenario: Only the attributes with values defined as variant attributes level 1 in the family variant are updated.
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-001;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
      code-002;code-001;clothing_color_size;master_men_blazers;Spring2017;description;Blazers_1654;100 EUR;blue;Blazers;composition;;;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then the product model "code-002" should not have the following values "collection, description-en_US-ecommerce, erp_name-en_US, price"

  Scenario: Import a file regardless of the product model order, first the root product model are imported then the sub product model
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;collection;description-en_US-ecommerce;erp_name-en_US;price;color;variation_name-en_US;composition;size;ean;sku;weight
      code-002;code-001;clothing_color_size;master_men_blazers;;;;;blue;Blazers;composition;;;;
      code-001;;clothing_color_size;master_men;Spring2017;description;Blazers_1654;100 EUR;;;;;;;
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
    And I should see the text "created 1"
    And I should see the text "skipped product model with parent 1"
    And I should see the text "skipped product model without parent 1"
    And I should see the text "read lines 2"

  Scenario: Successfully create product models without specifying the family variant of the sub product models
    Given the following CSV file to import:
      """
      code;family_variant;parent;name-en_US;collection;description-en_US-ecommerce;erp_name-en_US;price-EUR;supplier;wash_temperature;color;composition;variation_name-en_US;material;size
      new_hades;clothing_color_size;;Elegant slim long sleeve shirt;autumn_2016;Elegant slim long sleeve shirt white with button-down collar and breast pocket. 65% polyester, 35% cotton. Machine washable.;Hades;700;zaro;600;;;;;
      new_hades_blue;;new_hades;;;;;;;;blue;;Hades black;;
      new_hades_red;;new_hades;;;;;;;;red;;Hades red;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "read lines 3"
    And I should see the text "created 1"
    And I should see the text "created 2"
    And I should see the text "skipped product model with parent 2"
    And I should see the text "skipped product model without parent 1"

  Scenario: Successfully update product models without specifying the family variant of the sub product models
    Given the following CSV file to import:
      """
      code;family_variant;parent;name-en_US;collection;description-en_US-ecommerce;erp_name-en_US;price-EUR;supplier;wash_temperature;color;composition;variation_name-en_US;material;size
      hades;clothing_color_size;;Elegant slim long sleeve shirt;autumn_2016;Elegant slim long sleeve shirt white with button-down collar and breast pocket. 65% polyester, 35% cotton. Machine washable.;Hades;700;zaro;600;;;;;
      hades_blue;;hades;;;;;;;;blue;;Hades black;;
      hades_red;;hades;;;;;;;;red;;Hades red;;
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "read lines 3"
    And I should see the text "processed 1"
    And I should see the text "processed 2"
    And I should see the text "skipped product model with parent 2"
    And I should see the text "skipped product model without parent 1"

  Scenario: Successfully import root product models and sub product models with categories
    Given the following CSV file to import:
      """
      code;family_variant;categories;parent;color
      hades;clothing_color_size;tshirts;;
      hades_blue;;supplier_mongo,tshirts;hades;blue
      hades_red;;supplier_zaro,master_men;hades;red
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then there should be the following root product model:
      | code       | categories                       |
      | hades      | tshirts                          |
      | hades_blue | supplier_mongo,tshirts           |
      | hades_red  | master_men,supplier_zaro,tshirts |
