Feature: Apply "copy" action on variant product and product models
  In order to run the rules on variants
  As any user
  I need to be able to copy data on variant products and product models

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code        | label-en_US | type                         | localizable | scopable | group | decimals_allowed |
      | color       | Color       | pim_catalog_simpleselect     | 0           | 0        | other |                  |
      | description | Description | pim_catalog_textarea         | 1           | 1        | other |                  |
      | name        | Name        | pim_catalog_text             | 1           | 0        | other |                  |
      | price       | Price       | pim_catalog_price_collection | 0           | 0        | other | 1                |
      | size        | Size        | pim_catalog_simpleselect     | 0           | 0        | other |                  |
      | style       | Style       | pim_catalog_multiselect      | 0           | 0        | other |                  |
      | zipper      | Zipper      | pim_catalog_boolean          | 0           | 0        | other |                  |
    And the following "color" attribute options: red, yellow, black and white
    And the following "size" attribute options: s, m, l, xl
    And the following "style" attribute options: cheap, urban, with_zipper
    And the following family:
      | code | requirements-ecommerce | requirements-mobile | attributes                                         |
      | bags | sku                    | sku                 | color,description,name,price,size,sku,style,zipper |
    And the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1             | variant-axes_2 | variant-attributes_2 |
      | bag_one_level  | bags   | color,size     | color,description,price,size,sku |                |                      |
      | bag_two_levels | bags   | size           | description,size,style           | color          | color,price,sku      |
      | bag_unisize    | bags   | color          | color,price,sku                  |                |                      |
    And the following root product models:
      | code    | family_variant | description-en_US-ecommerce | name-en_US     | style       | zipper | size |
      | bag_1   | bag_one_level  |                             | Bag one level  | cheap,urban | 1      |      |
      | bag_2   | bag_two_levels |                             | Bag two levels |             | 1      |      |
      | bag_uni | bag_unisize    | A nice unisize bag          | Bag unisize    | urban       | 1      | s    |
    And the following sub product models:
      | code        | parent | size | description-en_US-ecommerce | style       |
      | bag_2_small | bag_2  | s    | A nice red bag              | cheap,urban |
    And the following products:
      | sku               | parent      | family | color | description-en_US-ecommerce   | price | size |
      | bag_1_large_black | bag_1       | bags   | black | A beautifull, big black bag   | 1 EUR | l    |
      | bag_1_small_white | bag_1       | bags   | white | A beautifull, small white bag |       | s    |
      | bag_2_small_red   | bag_2_small | bags   | red   |                               | 1 EUR |      |
      | bag_uni_red       | bag_uni     | bags   | red   |                               | 1 EUR |      |

  Scenario: Successfully copy value according to condition on product model
    Given the following product rule definitions:
      """
      copy_style:
        conditions:
          - field: zipper
            operator: =
            value: true
        actions:
          - type: copy
            from_field:  description
            from_locale: en_US
            from_scope:  ecommerce
            to_field:    description
            to_locale:   fr_FR
            to_scope:    mobile
      """
    When the product rule "copy_style" is executed
    Then there should be the following product model:
      | code        | description-fr_FR-mobile |
      | bag_2_small | A nice red bag           |
      | bag_uni     | A nice unisize bag       |
    And the product "bag_1_large_black" should have the following value:
      | description-fr_FR-mobile | A beautifull, big black bag |
    And the product "bag_1_small_white" should have the following value:
      | description-fr_FR-mobile | A beautifull, small white bag |
    But the product model "bag_1" should not have the following values "description-fr_FR-mobile"
    But the product model "bag_2" should not have the following values "description-fr_FR-mobile"
    And the variant product "bag_2_small_red" should not have the following value:
      | description-fr_FR-mobile |  |
    And the variant product "bag_uni_red" should not have the following value:
      | description-fr_FR-mobile |  |

  Scenario: Successfully copy value according to condition on variant product
    Given the following product rule definitions:
      """
      copy_price:
        conditions:
          - field: price
            operator: =
            value:
              amount: 1
              currency: EUR
        actions:
          - type: copy
            from_field:  description
            from_locale: en_US
            from_scope:  ecommerce
            to_field:    description
            to_locale:   fr_FR
            to_scope:    mobile
      """
    When the product rule "copy_price" is executed
    Then there should be the following product model:
      | code        | description-fr_FR-mobile |
      | bag_uni     |                          |
      | bag_2_small |                          |
    And the product "bag_1_large_black" should have the following value:
      | description-fr_FR-mobile | A beautifull, big black bag |
    But the product model "bag_1" should not have the following values "description-fr_FR-mobile"
    And the product model "bag_2" should not have the following values "description-fr_FR-mobile"
    And the variant product "bag_1_small_white" should not have the following value:
      | description-fr_FR-mobile |  |
    And the variant product "bag_2_small_red" should not have the following value:
      | description-fr_FR-mobile |  |
    And the variant product "bag_uni_red" should not have the following value:
      | description-fr_FR-mobile |  |

  Scenario: Successfully copy values according to conditions on both variant products and product models
    Given the following product rule definitions:
      """
      copy_price:
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
          - type: copy
            from_field:  description
            from_locale: en_US
            from_scope:  ecommerce
            to_field:    description
            to_locale:   fr_FR
            to_scope:    mobile
      """
    When the product rule "copy_price" is executed
    Then there should be the following product model:
      | code        | description-fr_FR-mobile |
      | bag_2_small | A nice red bag           |
    And the product "bag_1_small_white" should have the following value:
      | description-fr_FR-mobile | A beautifull, small white bag |
    But the product model "bag_1" should not have the following values "description-fr_FR-mobile"
    But the product model "bag_2" should not have the following values "description-fr_FR-mobile"
    But the product model "bag_uni" should not have the following values "description-fr_FR-mobile"
    And the variant product "bag_1_large_black" should not have the following value:
      | description-fr_FR-mobile |  |
    And the variant product "bag_2_small_red" should not have the following value:
      | description-fr_FR-mobile |  |
    And the variant product "bag_uni_red" should not have the following value:
      | description-fr_FR-mobile |  |
