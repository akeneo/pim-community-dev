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
    Then I should see that Name is a smart attribute

  Scenario: Successfully display smart attribute on a product model and a product
    Given a "default" catalog configuration
    And the following attributes:
      | code         | label-en_US  | type                     | localizable | scopable | group |
      | color        | Color        | pim_catalog_simpleselect | 0           | 0        | other |
      | description  | Description  | pim_catalog_textarea     | 1           | 1        | other |
      | name         | Name         | pim_catalog_text         | 1           | 0        | other |
      | size         | Size         | pim_catalog_simpleselect | 0           | 0        | other |
      | style        | Style        | pim_catalog_multiselect  | 0           | 0        | other |
      | variant_name | Variant name | pim_catalog_text         | 1           | 0        | other |
      | zipper       | Zipper       | pim_catalog_boolean      | 0           | 0        | other |
    And the following "color" attribute options: red, yellow, black and white
    And the following "size" attribute options: s, m, l, xl
    And the following "style" attribute options: with_zipper
    And the following family:
      | code | requirements-ecommerce | requirements-mobile | attributes                                                |
      | bags | sku                    | sku                 | color,description,name,size,sku,style,variant_name,zipper |
    And the following family variants:
      | code            | family | variant-axes_1 | variant-attributes_1 | variant-axes_2 | variant-attributes_2 |
      | bags_color_size | bags   | color          | color,variant_name   | size           | sku,size,description |
    And the following root product model:
      | code      | family_variant  | zipper |
      | bag_model | bags_color_size | 1      |
    And the following sub product model:
      | code      | parent    | zipper | color |
      | bag_black | bag_model | 1      | black |
    And the following product:
      | sku             | parent    | family | size |
      | bag_black_small | bag_black | bags   | s    |
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
      copy_name:
        conditions:
          - field: zipper
            operator: =
            value: true
        actions:
          - type: copy
            from_field: name
            to_field: variant_name
            from_locale: en_US
            to_locale: en_US
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
        labels:
          en_US: "Set Description"
      """
    When I am logged in as "Julia"
    And I am on the "bag_model" product model page
    Then I should see that Style is a smart attribute
    When I am on the "bag_black" product model page
    Then I should see that Variant name is a smart attribute
    But I should not see that Style is a smart attribute
    When I am on the "bag_black_small" product page
    Then I should see that Description is a smart attribute
    But I should not see that Style is a smart attribute
    And I should not see that Variant name is a smart attribute
