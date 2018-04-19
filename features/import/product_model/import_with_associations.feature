Feature: Import product models with associations
  In order to use existing product model information
  As a product manager
  I need to be able to import product models with associations

  Background:
    Given the "catalog_modeling" catalog configuration

  Scenario: Successfully import a csv file of product models with associations with comparison enabled
    And the following CSV file to import:
      """
      code;family_variant;image;UPSELL-products
      model-bikers-jacket;clothing_material_size;;watch
      """
    When the product models are imported via the job csv_catalog_modeling_product_model_import with options:
      | enabledComparison | yes |
    Then the product model "model-bikers-jacket" should have the following associations:
      | type   | products |
      | UPSELL | watch    |

  Scenario: Successfully import a csv file of product models with associations with comparison disabled
    And the following CSV file to import:
      """
      code;family_variant;image;UPSELL-products
      model-bikers-jacket;clothing_material_size;;watch
      """
    When the product models are imported via the job csv_catalog_modeling_product_model_import with options:
      | enabledComparison | no |
    Then the product model "model-bikers-jacket" should have the following associations:
      | type   | products |
      | UPSELL | watch    |
