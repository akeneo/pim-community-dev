Feature: Change the parent of a product model
  In order to manage product models
  As a catalog manager
  I would like to change the parent of a product model

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | localizable | scopable | group | decimals_allowed |
      | color | Color       | pim_catalog_simpleselect | 0           | 0        | other |                  |
      | size  | Size        | pim_catalog_simpleselect | 0           | 0        | other |                  |
    And the following "color" attribute options: blue, green, yellow, black and white
    And the following "size" attribute options: s, m, l, xl and xxl
    And the following family:
      | code    | requirements-ecommerce | requirements-mobile | attributes     |
      | tshirts | sku                    | sku                 | color,size,sku |
    And the following family variants:
      | code              | family  | variant-axes_1 | variant-attributes_1 | variant-axes_2 | variant-attributes_2 |
      | tshirts_variant   | tshirts | size           | size,sku             |                |                      |
      | tshirts_variant_2 | tshirts | color          | color                | size           | size,sku             |

  Scenario: Successfully change the parent of a product model
    Given the following root product models:
      | code       | family_variant    |
      | round_neck | tshirts_variant_2 |
      | v_neck     | tshirts_variant_2 |
    And the following sub product model:
      | code             | parent     | family_variant    | color |
      | white_round_neck | round_neck | tshirts_variant_2 | white |
    When the parent of product model white_round_neck is changed for root product model v_neck
    Then the parent of the product model white_round_neck should be v_neck

  Scenario: Fail to change the parent of a product model with parent with a different family variant
    Given the following root product models:
      | code       | family_variant    |
      | round_neck | tshirts_variant_2 |
      | v_neck     | tshirts_variant |
    And the following sub product model:
      | code             | parent     | family_variant    | color |
      | white_round_neck | round_neck | tshirts_variant_2 | white |
    When the parent of product model white_round_neck is changed for invalid root product model v_neck
    Then the parent of the product model white_round_neck should be round_neck
