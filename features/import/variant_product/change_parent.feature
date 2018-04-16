Feature: Change a variant product parent through product import
  In order to change a variant product parent
  As a catalog manager
  I would like to change the parent of a variant product by import

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | localizable | scopable | group | decimals_allowed |
      | color | Color       | pim_catalog_simpleselect | 0           | 0        | other |                  |
      | size  | Size        | pim_catalog_simpleselect | 0           | 0        | other |                  |
    And the following "color" attribute options: blue, green, yellow, black and white
    And the following "size" attribute options: s, m, l, xl and xxl
    And the following family:
      | code    | requirements-ecommerce | requirements-mobile | attributes |
      | bags    | sku                    | sku                 | color,sku  |
      | tshirts | sku                    | sku                 | size,sku   |
    And the following family variants:
      | code            | family  | variant-axes_1 | variant-attributes_1 |
      | bags_variant    | bags    | color          | color,sku            |
      | tshirts_variant | tshirts | size           | size,sku             |
    And the following root product models:
      | code  | categories | family_variant  |
      | james | default    | bags_variant    |
      | rita  | default    | bags_variant    |
      | basic | default    | tshirts_variant |
      | tall  | default    | tshirts_variant |
    And the following products:
      | sku          | parent | family  | categories | color  | size |
      | bag_yellow | james  | bags    | default    | yellow |      |
      | bag_white  | james  | bags    | default    | white  |      |
      | bag_black  | james  | bags    | default    | black  |      |
      | bag_blue   | james  | bags    | default    | blue   |      |
      | bag_green  | james  | bags    | default    | green  |      |
      | tshirt_s     | basic  | tshirts | default    |        | s    |
      | tshirt_m     | basic  | tshirts | default    |        | m    |
      | tshirt_l     | basic  | tshirts | default    |        | l    |
      | tshirt_xl    | tall   | tshirts | default    |        | xl   |
      | tshirt_xxl   | tall   | tshirts | default    |        | xxl  |

  Scenario: Add to a new product model some variant products coming from an existing product model
    Given the following CSV file to import:
      """
      parent;sku
      rita;bag_yellow
      rita;bag_blue
      rita;bag_green
      """
    When the products are imported via the job csv_default_product_import
    Then the parent of the product bag_yellow should be rita
    And the parent of the product bag_blue should be rita
    And the parent of the product bag_green should be rita

  Scenario: Put every variant products from 2 product models into one
    Given the following CSV file to import:
      """
      parent;sku
      basic;tshirt_xl
      basic;tshirt_xxl
      """
    When the products are imported via the job csv_default_product_import
    Then the parent of the product tshirt_xl should be basic
    And the parent of the product tshirt_xxl should be basic
    And product model tall should not have any children
