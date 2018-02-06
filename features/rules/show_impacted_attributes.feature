@javascript
Feature: On a product edit/show display impacted attributes
  In order to know which attributes are affected or not
  As a regular user
  I need to see which attributes are affected by a rule or not

  Scenario: Successfully display smart attribute on a non variant product
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And the following products:
      | sku       | family  |
      | my-loafer | sandals |
    And the following product rule definitions:
      """
      set_rule:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-loafer
        actions:
          - type:   set
            field:  name
            value:  My loafer
            locale: en_US
      """
    When I am logged in as "Julia"
    And I am on the "my-loafer" product page
    Then I should see that Name is a smart

  Scenario: Successfully display smart attribute on a product model and a product
    Given a "default" catalog configuration
    And the following attributes:
      | code        | label-en_US | type                     | localizable | scopable | group |
      | color       | Color       | pim_catalog_simpleselect | 0           | 0        | other |
      | description | Description | pim_catalog_textarea     | 1           | 1        | other |
      | name        | Name        | pim_catalog_text         | 1           | 0        | other |
      | size        | Size        | pim_catalog_simpleselect | 0           | 0        | other |
      | style       | Style       | pim_catalog_multiselect  | 0           | 0        | other |
      | zipper      | Zipper      | pim_catalog_boolean      | 0           | 0        | other |
    And the following "color" attribute options: red, yellow, black and white
    And the following "size" attribute options: s, m, l, xl
    And the following "style" attribute options: with_zipper
    And the following family:
      | code | requirements-ecommerce | requirements-mobile | attributes                                   |
      | bags | sku                    | sku                 | color,description,name,size,sku,style,zipper |
    And the following family variants:
      | code            | family | variant-axes_1 | variant-attributes_1       |
      | bags_color_size | bags   | color,size     | color,description,size,sku |
    And the following root product models:
      | code      | family_variant  | zipper |
      | bag_model | bags_color_size | 1      |
    And the following products:
      | sku             | parent    | family | color | size |
      | bag_large_black | bag_model | bags   | black | s    |
    And the following product rule definitions:
      """
      set_style:
        conditions:
          - field: zipper
            operator: =
            value: true
        actions:
          - type: add
            field: style
            items:
              - with_zipper
      set_description:
        conditions:
          - field: zipper
            operator: =
            value: true
        actions:
          - type: set
            field: description
            locale: en_US
            scope: ecommerce
            value: "A nice description"
      """
    When I am logged in as "Julia"
    And I am on the "bag_model" product model page
    Then I should see that Style is a smart
    When I am on the "bag_large_black" product page
    Then I should see that Style is a smart
    And I should see that Description is a smart
