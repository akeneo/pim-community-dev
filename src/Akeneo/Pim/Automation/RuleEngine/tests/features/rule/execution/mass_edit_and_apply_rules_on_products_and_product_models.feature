Feature: Apply rules after a mass edit execution
  In order to have fully modified products and product models after a mass edit
  As a product manager
  I need to have rules launched after a mass edit

  Background:
    Given the "default" catalog configuration
    And the following categories:
      | code      | parent  | label-en_US                 |
      | no_chance | default | No chance for city thieves! |
      | small     | default | Small                       |
    And the following attributes:
      | code        | label-en_US | type                     | localizable | scopable | group | decimals_allowed |
      | color       | Color       | pim_catalog_simpleselect | 0           | 0        | other |                  |
      | description | Description | pim_catalog_textarea     | 1           | 1        | other |                  |
      | name        | Name        | pim_catalog_text         | 1           | 0        | other |                  |
      | size        | Size        | pim_catalog_simpleselect | 0           | 0        | other |                  |
      | style       | Style       | pim_catalog_multiselect  | 0           | 0        | other |                  |
    And the following "color" attribute options: red, yellow, black, white
    And the following "size" attribute options: s, m, l, xl
    And the following "style" attribute options: cheap, urban
    And the following family:
      | code | attribute_requirements | attributes                            |
      | bags | ecommerce-sku          | color,description,name,size,sku,style |
    And the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1       | variant-axes_2 | variant-attributes_2 |
      | bag_one_level  | bags   | color,size     | color,description,size,sku |                |                      |
      | bag_two_levels | bags   | size           | description,size,style     | color          | color,sku            |
      | bag_unisize    | bags   | color          | color,description,sku      |                |                      |
    And the following root product models:
      | code    | categories | family_variant | name-en_US     | style       | size |
      | bag_1   | default    | bag_one_level  | Bag one level  | cheap,urban |      |
      | bag_2   | default    | bag_two_levels | Bag two levels |             |      |
      | bag_uni | default    | bag_unisize    | Bag unisize    | cheap,urban | s    |
    And the following sub product models:
      | code        | parent | size | description-en_US-ecommerce | style       |
      | bag_2_small | bag_2  | s    | A nice red bag              | cheap,urban |
    And the following products:
      | sku               | parent      | family | categories | color | size | description-en_US-ecommerce | name-en_US  | style |
      | bag_1_large_black | bag_1       | bags   | default    | black | s    | A nice, big black bag       |             |       |
      | bag_2_small_red   | bag_2_small | bags   | default    | red   |      |                             |             |       |
      | bag_uni_red       | bag_uni     | bags   | default    | red   |      | A red bag                   |             |       |
      | a_bag             |             | bags   | default    | red   |      | A very simple bag           | A bag       | cheap |
      | another_bag       |             | bags   | default    | red   |      | A completely useless bag    | Another bag | cheap |

  @integration-back
  Scenario: Successfully apply rules after a mass edit operation only on edited product models
    Given the following product rule definitions:
      """
      set_description:
        priority: 10
        conditions:
          - field: style
            operator: IN
            value:
              - cheap
        actions:
          - type:   set
            field:  description
            value:  A cheap bag
            locale: en_US
            scope:  ecommerce
      """
    When I execute an edit attribute values bulk action to set the en_US unscoped name to "Just a bag" for "bag_1,bag_uni,a_bag"
    Then the en_US unscoped name of "bag_1" should be "Just a bag"
    And the en_US unscoped name of "bag_2" should be "Bag two levels"
    And the en_US unscoped name of "bag_uni" should be "Just a bag"
    And the en_US ecommerce description of "bag_1_large_black" should be "A cheap bag"
    And the en_US ecommerce description of "bag_uni_red" should be "A cheap bag"
    And the en_US unscoped name of "a_bag" should be "Just a bag"
    And the en_US ecommerce description of "a_bag" should be "A cheap bag"
    But the en_US ecommerce description of "bag_2_small" should be "A nice red bag"
    And the en_US unscoped name of "another_bag" should be "Another bag"
    And the en_US ecommerce description of "another_bag" should be "A completely useless bag"
