Feature: Execute a job
  In order to use existing product information
  As a product manager
  I need to be able to import products with associations

  Background:
    Given the "catalog_modeling" catalog configuration
    And the following product groups:
      | code  | label-en_US | type    |
      | CROSS | Bag Cross   | RELATED |
  Scenario: Successfully import a csv file of products with product model associations
    Given the following CSV file to import:
      """
      sku;family;X_SELL-product_models;UPSELL-product_models
      SKU-001;clothing;amor,brooksblue;
      1111111171;accessories;;brookspink
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the product "SKU-001" should have the following associations:
      | type   | product_models   |
      | X_SELL | amor,brooksblue |
    And the product "1111111171" should have the following associations:
      | type   | product_models   |
      | UPSELL | brookspink      |

  @jira https://akeneo.atlassian.net/browse/PIM-7852
  Scenario: Successfully import a csv file of products with product model associations using a job with comparison disabled
    Given the following CSV file to import:
      """
      sku;family;X_SELL-product_models;UPSELL-product_models
      SKU-001;clothing;amor,brooksblue;
      1111111171;accessories;;brookspink
      """
    And the following job "csv_catalog_modeling_product_import" configuration:
      | enabledComparison | no |
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the product "SKU-001" should have the following associations:
      | type   | product_models   |
      | X_SELL | amor,brooksblue |
    And the product "1111111171" should have the following associations:
      | type   | product_models   |
      | UPSELL | brookspink      |
