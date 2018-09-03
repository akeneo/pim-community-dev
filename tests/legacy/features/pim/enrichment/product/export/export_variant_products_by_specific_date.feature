@javascript
Feature: Export updated variant products according to a date
  In order to use the enriched product data
  As a product manager
  I need to be able to export the variant products according to a date

  Scenario: Export only the variant products which parent has been updated since the last export
    Given a "catalog_modeling" catalog configuration
    And the following job "csv_catalog_modeling_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                                                                                          |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "updated", "operator": "SINCE LAST JOB", "value": "csv_catalog_modeling_product_export"}]} |
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
      sku;categories;enabled;family;parent;groups;brand;collection;color;composition;description-en_US-mobile;ean;erp_name-en_US;image;keywords-en_US;material;meta_description-en_US;meta_title-en_US;name-en_US;notice;PACK-groups;PACK-products;PACK-product_models;price-EUR;price-USD;size;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;supplier;UPSELL-groups;UPSELL-products;UPSELL-product_models;variation_image;variation_name-en_US;weight;weight-unit;X_SELL-groups;X_SELL-products;X_SELL-product_models
      braided-hat-m;master_accessories_hats;1;accessories;model-braided-hat;;;summer_2017;battleship_grey;;;1234567890348;;files/braided-hat-m/image/braided-hat.jpg;;wool;;;"Braided hat ";;;;;;;m;;;;;;;;;"Braided hat medium";700.0000;GRAM;;;
      braided-hat-xxxl;master_accessories_hats;1;accessories;model-braided-hat;;;summer_2017;battleship_grey;;;1234567890349;;files/braided-hat-xxxl/image/braided-hat.jpg;;wool;;;"Braided hat ";;;;;;;xxxl;;;;;;;;;"Braided hat large";700.0000;GRAM;;;
      """

