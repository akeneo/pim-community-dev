Feature: Apply "set" action on variant product and product models
  In order to run the rules on variants
  As any user
  I need to be able to set data on variant products and product models

  Background:
    Given a "default" catalog configuration
    And the following categories:
      | code      | parent  | label-en_US                 |
      | no_chance | default | No chance for city thieves! |
      | small     | default | Small                       |
    And the following attributes:
      | code        | label-en_US | type                         | localizable | scopable | group | decimals_allowed |
      | color       | Color       | pim_catalog_simpleselect     | 0           | 0        | other |                  |
      | description | Description | pim_catalog_textarea         | 1           | 1        | other |                  |
      | name        | Name        | pim_catalog_text             | 1           | 0        | other |                  |
      | price       | Price       | pim_catalog_price_collection | 0           | 0        | other | 1                |
      | size        | Size        | pim_catalog_simpleselect     | 0           | 0        | other |                  |
      | style       | Style       | pim_catalog_multiselect      | 0           | 0        | other |                  |
      | zipper      | Zipper      | pim_catalog_boolean          | 0           | 0        | other |                  |
    And the following "color" attribute options: red, yellow, black, white
    And the following "size" attribute options: s, m, l, xl
    And the following "style" attribute options: with_zipper, cheap, urban
    And the following family:
      | code | attribute_requirements   | attributes                                         |
      | bags | ecommerce-sku,mobile-sku | color,description,name,price,size,sku,style,zipper |
    And the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1             | variant-axes_2 | variant-attributes_2 |
      | bag_one_level  | bags   | color,size     | color,description,price,size,sku |                |                      |
      | bag_two_levels | bags   | size           | description,size,style           | color          | color,price,sku      |
      | bag_unisize    | bags   | color          | color,description,price,sku      |                |                      |
    And the following root product models:
      | code    | categories | family_variant | name-en_US     | style       | zipper | size |
      | bag_1   | default    | bag_one_level  | Bag one level  | cheap,urban | 1      |      |
      | bag_2   | default    | bag_two_levels | Bag two levels |             | 1      |      |
      | bag_uni | default    | bag_unisize    | Bag unisize    | urban       | 1      | s    |
    And the following sub product models:
      | code        | parent | size | description-en_US-ecommerce | style       |
      | bag_2_small | bag_2  | s    | A nice red bag              | cheap,urban |
    And the following products:
      | sku               | parent      | family | categories | color | size |
      | bag_1_large_black | bag_1       | bags   | default    | black | s    |
      | bag_2_small_red   | bag_2_small | bags   | default    | red   |      |
      | bag_uni_red       | bag_uni     | bags   | default    | red   |      |

  @integration-back
  Scenario: Successfully set value on product models
    Given the following product rule definitions:
      """
      set_style:
        conditions:
          - field: zipper
            operator: =
            value: true
        actions:
          - type: set
            field: style
            value:
              - with_zipper
      """
    When the product rule "set_style" is executed
    Then there should be the following product model:
      | code        | style         |
      | bag_1       | [with_zipper] |
      | bag_2_small | [with_zipper] |
      | bag_uni     | [with_zipper] |
    But the "bag_2" product model should not have the following values "style"
    And the "bag_1_large_black" variant product should not have the following value:
      | style | [with_zipper] |
    And the "bag_2_small_red" variant product should not have the following value:
      | style | [with_zipper] |
    And the "bag_uni_red" variant product should not have the following value:
      | style | [with_zipper] |

  @integration-back
  Scenario: Successfully set categories on product models
    Given the following product rule definitions:
      """
      set_category:
        conditions:
          - field: zipper
            operator: =
            value: true
          - field: style
            operator: IN
            value:
              - urban
        actions:
          - type: set
            field: categories
            value:
              - no_chance
      """
    When the product rule "set_category" is executed
    Then the category of the "bag_1" product model should be "no_chance"
    And the category of the "bag_1_large_black" product should be "no_chance"
    And the category of the "bag_uni" product model should be "no_chance"
    And the category of the "bag_uni_red" product should be "no_chance"
    But the category of the "bag_2" product model should be "default"
    And the category of the "bag_2_small" product model should be "default"
    And the category of the "bag_2_small_red" product should be "default"

  @integration-back
  Scenario: Successfully set value on variant product with condition on product model
    Given the following product rule definitions:
      """
      set_price:
        conditions:
          - field: style
            operator: IN
            value:
              - cheap
        actions:
          - type: set
            field: price
            value:
              - amount: 1
                currency: EUR
      """
    When the product rule "set_price" is executed
    Then the "bag_1_large_black" product should have the following value:
      | price | 1.00 EUR |
    And the "bag_2_small_red" product should have the following value:
      | price | 1.00 EUR |
    But the "bag_1" product model should not have the following values "price"
    And the "bag_2" product model should not have the following values "price"
    And the "bag_2_small" product model should not have the following values "price"
    And the "bag_uni" product model should not have the following values "price"
    And the "bag_uni_red" variant product should not have the following values:
      | style | [with_zipper] |

  @integration-back @purge-messenger
  Scenario: Successfully set values according to conditions on both variant products and product models
    Given the following product rule definitions:
      """
      set_price:
        conditions:
          - field: style
            operator: IN
            value:
              - cheap
          - field: size
            operator: IN
            value:
              - s
        actions:
          - type: set
            field: price
            value:
              - amount: 1
                currency: EUR
      """
    When the product rule "set_price" is executed
    Then the "bag_1_large_black" product should have the following value:
      | price | 1.00 EUR |
    And the "bag_2_small_red" product should have the following value:
      | price | 1.00 EUR |
    But the "bag_1" product model should not have the following values "price"
    And the "bag_2" product model should not have the following values "price"
    And the "bag_2_small" product model should not have the following values "price"
    And the "bag_uni" product model should not have the following values "price"
    And the "bag_uni_red" variant product should not have the following values:
      | price | 1.00 EUR |
    And 2 events of type "product.updated" should have been raised

  @integration-back
  Scenario: Successfully set categories according to conditions on both variant products and product models
    Given the following product rule definitions:
      """
      set_category:
        conditions:
          - field: zipper
            operator: =
            value: true
          - field: size
            operator: IN
            value:
              - s
        actions:
          - type: set
            field: categories
            value:
              - small
      """
    When the product rule "set_category" is executed
    Then the category of the "bag_uni" product model should be "small"
    And the category of the "bag_uni_red" product should be "small"
    But the category of the "bag_1" product model should be "default"
    And the category of the "bag_1_large_black" product should be "default"
    And the category of the "bag_2" product model should be "default"
    And the category of the "bag_2_small" product model should be "default"
    And the category of the "bag_2_small_red" product should be "default"
