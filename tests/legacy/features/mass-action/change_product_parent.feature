Feature: Change the parent of a variant product
  In order to manage variant product and product models
  As a catalog manager
  I would like to change the parent of a variant product or a sub product model using a bulk action

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | localizable | scopable | group | decimals_allowed | useable_as_grid_filter |
      | color | Color       | pim_catalog_simpleselect | 0           | 0        | other |                  | yes                    |
      | size  | Size        | pim_catalog_simpleselect | 0           | 0        | other |                  | yes                    |
    And the following "color" attribute options: blue, yellow and white
    And the following "size" attribute options: s, m, l, xl and xxl
    And the following family:
      | code    | requirements-ecommerce | requirements-mobile | attributes     |
      | bags    | sku                    | sku                 | color,sku      |
      | tshirts | sku                    | sku                 | color,size,sku |
    And the following family variants:
      | code              | family  | variant-axes_1 | variant-attributes_1 | variant-axes_2 | variant-attributes_2 |
      | bags_variant      | bags    | color          | color,sku            |                |                      |
      | tshirts_variant   | tshirts | size           | size,sku             |                |                      |
      | tshirts_variant_2 | tshirts | color          | color                | size           | size,sku             |

  Scenario: Successfully change the parent of several variant products
    Given the following root product models:
      | code  | family_variant |
      | james | bags_variant   |
      | rita  | bags_variant   |
    And the following products:
      | sku        | parent | family | color  |
      | bag_yellow | james  | bags   | yellow |
      | bag_white  | james  | bags   | white  |
    When i massively change the parent of the products bag_yellow and bag_white for rita
    Then the parent of the product bag_yellow should be rita
    And the parent of the product bag_white should be rita

  Scenario: Successfully change the parent of several sub product models
    Given the following root product models:
      | code       | family_variant    |
      | round_neck | tshirts_variant_2 |
      | v_neck     | tshirts_variant_2 |
    And the following sub product model:
      | code             | parent     | family_variant    | color |
      | white_round_neck | round_neck | tshirts_variant_2 | white |
      | blue_round_neck  | round_neck | tshirts_variant_2 | blue  |
    When i massively change the parent of the product models white_round_neck and blue_round_neck for v_neck
    Then the parent of the product model white_round_neck should be v_neck
    And the parent of the product model blue_round_neck should be v_neck
