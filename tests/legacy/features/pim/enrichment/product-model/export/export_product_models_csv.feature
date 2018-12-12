@javascript
Feature: Export variant products through CSV export
  In order to export shoes of the collection 2016 to my ecommerce channel
  As a product manager
  I need to be able to export product models as a CSV file

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  @critical
  Scenario: Export product models through CSV
    Given the following associations for the product model "amor":
      | type   | products   |
      | X_SELL | 1111111175 |
      | X_SELL | 1111111176 |
      | UPSELL | 1111111177 |
    And the following associations for the product model "plain":
      | type   | products   |
      | X_SELL | 1111111175 |
    And the following associations for the product model "plain_red":
      | type   | products   |
      | X_SELL | 1111111176 |
    And I am on the "csv_product_model_export" export job edit page
    And I visit the "Content" tab
    And I filter by "identifier" with operator "" and value "amor, plain, plain_red"
    And I press the "Save" button
    And I should not see the text "There are unsaved changes"
    And I launch the export job
    And I wait for the "csv_product_model_export" job to finish
    Then exported file of "csv_product_model_export" should contain the lines:
      """
      code;family_variant;parent;categories;brand;care_instructions;collection;color;composition;description-en_US-ecommerce;erp_name-en_US;image;keywords-en_US;material;meta_description-en_US;meta_title-en_US;name-en_US;notice;PACK-groups;PACK-products;PACK-product_models;price-EUR;price-USD;SUBSTITUTION-groups;SUBSTITUTION-products;SUBSTITUTION-product_models;supplier;UPSELL-groups;UPSELL-products;UPSELL-product_models;variation_image;variation_name-en_US;wash_temperature;weight;weight-unit;X_SELL-groups;X_SELL-products;X_SELL-product_models
      amor;clothing_colorsize;;master_men_blazers,supplier_zaro;;;summer_2016;;;"Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR";Amor;;;;;;"Heritage jacket navy";;;;;999.00;;;;;zaro;;1111111177;;;;800;;;;1111111175,1111111176;
      plain;clothing_color_size;;tshirts;;;summer_2017;;;;Plain;;;;;;plain;;;;;;;;;;;;;;;;;;;;1111111175;
      plain_red;clothing_color_size;plain;tshirts;;;summer_2017;red;;;Plain;;;;;;plain;;;;;;;;;;;;;;files/plain_red/variation_image/plain_red.jpg;"Plain red ";;;;;1111111175,1111111176;
      """
