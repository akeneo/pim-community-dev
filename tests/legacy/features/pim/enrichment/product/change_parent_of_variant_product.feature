Feature: Change the parent of a variant product
  In order to manage variant product
  As a catalog manager
  I would like to change the parent of a variant product

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
      | bags    | sku                    | sku                 | color,sku      |
      | tshirts | sku                    | sku                 | color,size,sku |
    And the following family variants:
      | code              | family  | variant-axes_1 | variant-attributes_1 | variant-axes_2 | variant-attributes_2 |
      | bags_variant      | bags    | color          | color,sku            |                |                      |
      | tshirts_variant   | tshirts | size           | size,sku             |                |                      |
      | tshirts_variant_2 | tshirts | color          | color                | size           | size,sku             |

  Scenario: Add to a new product model some variant products coming from an existing product model
    Given the following root product models:
      | code  | family_variant |
      | james | bags_variant   |
      | rita  | bags_variant   |
    And the following products:
      | sku        | parent | family | color  |
      | bag_yellow | james  | bags   | yellow |
    When the parent of variant product bag_yellow is changed for rita product model
    Then the parent of the product bag_yellow should be rita

  Scenario: Put every variant products from 2 product models into one
    Given the following root product models:
      | code  | family_variant  |
      | james | bags_variant    |
      | rita  | bags_variant    |
      | basic | tshirts_variant |
      | tall  | tshirts_variant |
    And the following products:
      | sku        | parent | family  | color  | size |
      | bag_yellow | james  | bags    | yellow |      |
      | bag_white  | james  | bags    | white  |      |
      | bag_black  | james  | bags    | black  |      |
      | bag_blue   | james  | bags    | blue   |      |
      | bag_green  | james  | bags    | green  |      |
      | tshirt_s   | basic  | tshirts |        | s    |
      | tshirt_m   | basic  | tshirts |        | m    |
      | tshirt_l   | basic  | tshirts |        | l    |
      | tshirt_xl  | tall   | tshirts |        | xl   |
      | tshirt_xxl | tall   | tshirts |        | xxl  |
    When the parents of the following products are changed:
      | sku        | parent |
      | bag_yellow | rita   |
      | bag_blue   | rita   |
      | bag_green  | rita   |
      | tshirt_xl  | basic  |
      | tshirt_xxl | basic  |
    Then the parent of the product bag_yellow should be rita
    And the parent of the product bag_blue should be rita
    And the parent of the product bag_green should be rita
    And the parent of the product tshirt_xl should be basic
    And the parent of the product tshirt_xxl should be basic
    And product model tall should not have any children

  Scenario: Fail to change the level of the variant product parent
    Given the following root product models:
      | code       | family_variant    |
      | round_neck | tshirts_variant_2 |
    And the following sub product model:
      | code             | parent     | family_variant    | color |
      | white_round_neck | round_neck | tshirts_variant_2 | white |
    And the following product:
      | sku                    | parent           | family  | size |
      | small_white_round_neck | white_round_neck | tshirts | s    |
    When the parent of variant product small_white_round_neck is changed for incorrect round_neck product model
    Then the parent of the product small_white_round_neck should be white_round_neck

  Scenario: Fail to change the variant product parent if new model already has a variant product with same axis value
    Given the following root product models:
      | code  | family_variant |
      | james | bags_variant   |
      | paul  | bags_variant   |
    And the following product:
      | sku          | parent | family | color  |
      | yellow_james | james  | bags   | yellow |
      | yellow_paul  | paul   | bags   | yellow |
    When the parent of variant product yellow_james is changed for incorrect paul product model
    Then the parent of the product yellow_james should be james

  Scenario: Changing the parent of a variant product creates a new version of this product
    Given the following root product models:
      | code  | family_variant |
      | james | bags_variant   |
      | rita  | bags_variant   |
    And the following products:
      | sku        | parent | family | color  |
      | bag_yellow | james  | bags   | yellow |
    When the parent of variant product bag_yellow is changed for rita product model
    Then the last version of the variant product bag_yellow should be:
      | field  | old_value | new_value |
      | parent | james     | rita      |
