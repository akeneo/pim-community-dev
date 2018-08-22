@javascript
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
    And the following "color" attribute options: red, yellow, black and white
    And the following "size" attribute options: s, m, l, xl
    And the following "style" attribute options: cheap, urban
    And the following family:
      | code | requirements-ecommerce | requirements-ecommerce | attributes                            |
      | bags | sku                    | sku                    | color,description,name,size,sku,style |
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
    And I am logged in as "Julia"

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
    When I am on the products grid
    And I select rows bag_1, bag_uni and a_bag
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Name attribute
    And I change the "Name" to "Just a bag"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then there should be the following root product model:
      | code    | name-en_US     |
      | bag_1   | Just a bag     |
      | bag_2   | Bag two levels |
      | bag_uni | Just a bag     |
    And the product "bag_1_large_black" should have the following values:
      | description-en_US-ecommerce | A cheap bag |
    And the product "bag_uni_red" should have the following values:
      | description-en_US-ecommerce | A cheap bag |
    And the product "a_bag" should have the following values:
      | name-en_US                  | Just a bag  |
      | description-en_US-ecommerce | A cheap bag |
    But there should be the following product model:
      | code        | description-en_US-ecommerce |
      | bag_2_small | A nice red bag              |
    And the product "another_bag" should have the following values:
      | name-en_US                  | Another bag              |
      | description-en_US-ecommerce | A completely useless bag |
