@javascript
Feature: Export variant products through XLSX export
  In order to export shoes of the collection 2016 to my ecommerce channel
  As a product manager
  I need to be able to export product models as a XLSX file

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Export product models through XLSX
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
    And I am on the "xlsx_summer_2016_models_export" export job page
    And I launch the export job
    And I wait for the "xlsx_summer_2016_models_export" job to finish
    Then exported xlsx file of "xlsx_summer_2016_models_export" should contain the lines:
      | code      | family_variant      | parent | categories                       | X_SELL-groups | X_SELL-products       | X_SELL-product_models | UPSELL-groups | UPSELL-products | UPSELL-product_models | SUBSTITUTION-groups | SUBSTITUTION-products | SUBSTITUTION-product_models | PACK-groups | PACK-products | PACK-product_models | COMPATIBILITY-groups | COMPATIBILITY-products | COMPATIBILITY-product_models | brand | care_instructions | collection  | color | composition | description-en_US-ecommerce                                                                                                                                                                                                     | erp_name-en_US | eu_shoes_size | image | keywords-en_US | material | meta_description-en_US | meta_title-en_US | name-en_US           | notice | price-EUR | price-USD | size | sole_composition | supplier | top_composition | variation_image                               | variation_name-en_US | wash_temperature | weight | weight-unit |
      | amor      | clothing_colorsize  |        | master_men_blazers,supplier_zaro |               | 1111111175,1111111176 |                       |               | 1111111177      |                       |                     |                       |                             |             |               |                     |                      |                        |                              |       |                   | summer_2016 |       |             | Heritage jacket navy blue tweed suit with single breasted 2 button. 53% wool, 22% polyester, 18% acrylic, 5% nylon, 1% cotton, 1% viscose. Dry Cleaning uniquement.Le mannequin measuring 1m85 and wears UK size 40, size 50 FR | Amor           |               |       |                |          |                        |                  | Heritage jacket navy |        | 999.00    |           |      |                  | zaro     |                 |                                               |                      | 800              |        |             |
      | plain_red | clothing_color_size | plain  | tshirts                          |               | 1111111175,1111111176 |                       |               |                 |                       |                     |                       |                             |             |               |                     |                      |                        |                              |       |                   | summer_2017 | red   |             |                                                                                                                                                                                                                                 | Plain          |               |       |                |          |                        |                  | plain                |        |           |           |      |                  |          |                 | files/plain_red/variation_image/plain_red.jpg | "Plain red "         |                  |        |             |
      | plain     | clothing_color_size |        | tshirts                          |               | 1111111175            |                       |               |                 |                       |                     |                       |                             |             |               |                     |                      |                        |                              |       |                   | summer_2017 |       |             |                                                                                                                                                                                                                                 | Plain          |               |       |                |          |                        |                  | plain                |        |           |           |      |                  |          |                 |                                               |                      |                  |        |             |
