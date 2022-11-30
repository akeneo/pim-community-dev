@javascript
Feature: Export updated variant products according to a date
  In order to use the enriched product data
  As a product manager
  I need to be able to export the variant products according to a date

  Scenario: Export only the variant products which parent has been updated since the last export
    Given a "catalog_modeling" catalog configuration
    And the following job "csv_catalog_modeling_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/product_export.csv"}                                                                                        |
      | filters   | {"structure":{"locales":["en_US"],"scope":"mobile","attributes":["collection","color"]},"data":[{"field": "updated", "operator": "SINCE LAST JOB", "value": "csv_catalog_modeling_product_export"}]} |
      | with_uuid | no                                                                                                                                                               |
    And I am logged in as "Julia"
    When I am on the "csv_catalog_modeling_product_export" export job page
    And I launch the export job
    And I wait for the "csv_catalog_modeling_product_export" job to finish
    And I edit the "model-braided-hat" product model
    And I change the "Model description" to "Nice hat!"
    And I save the product model
    And I should not see the text "There are unsaved changes"
    And I am on the "csv_catalog_modeling_product_export" export job page
    And I launch the export job
    And I wait for the "csv_catalog_modeling_product_export" job to finish
    Then exported file of "csv_catalog_modeling_product_export" should contain:
      """
      sku;categories;enabled;family;parent;groups;X_SELL-groups;X_SELL-products;X_SELL-product_models;UPSELL-groups;UPSELL-products;UPSELL-product_models;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;PACK-groups;PACK-products;PACK-product_models;COMPATIBILITY-groups;COMPATIBILITY-products;COMPATIBILITY-product_models;collection;color
      braided-hat-m;master_accessories_hats;1;accessories;model-braided-hat;;;;;;;;;;;;;;;;;summer_2017;battleship_grey
      braided-hat-xxxl;master_accessories_hats;1;accessories;model-braided-hat;;;;;;;;;;;;;;;;;summer_2017;battleship_grey
      """
