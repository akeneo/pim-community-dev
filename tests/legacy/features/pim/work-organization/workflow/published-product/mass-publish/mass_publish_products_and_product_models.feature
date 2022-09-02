@javascript @published-product-feature-enabled
Feature: Publish many products at once by skipping the product models
  In order to freeze the product data I would use to export
  As a product manager
  I need to be able to publish several products at the same time by skipping the product models

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | localizable | scopable | group |
      | color | Color       | pim_catalog_simpleselect | 0           | 0        | other |
      | size  | Size        | pim_catalog_simpleselect | 0           | 0        | other |
    And the following "color" attribute options: red and black
    And the following "size" attribute options: s and l
    And the following family:
      | code | requirements-ecommerce | requirements-mobile | attributes     |
      | bags | sku                    | sku                 | color,size,sku |
    And the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1 | variant-axes_2 | variant-attributes_2 |
      | bag_one_level  | bags   | color,size     | color,size,sku       |                |                      |
      | bag_two_levels | bags   | size           | size                 | color          | color,sku            |
    And the following root product models:
      | code  | categories | family_variant |
      | bag_1 | default    | bag_one_level  |
      | bag_2 | default    | bag_two_levels |
    And the following sub product models:
      | code        | parent | size |
      | bag_2_small | bag_2  | s    |
      | bag_2_large | bag_2  | l    |
    And the following products:
      | sku               | parent      | family | color | size |
      | bag_1_large_black | bag_1       | bags   | black | s    |
      | bag_1_large_red   | bag_1       | bags   | red   | s    |
      | bag_2_small_red   | bag_2_small | bags   | red   |      |
      | bag_2_large_red   | bag_2_large | bags   | red   |      |
      | bag_2_large_black | bag_2_large | bags   | black |      |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Successfully publish variant products of selected root product models
    Given I select rows bag_1 and bag_2
    And I press the "Bulk actions" button
    And I choose the "Publish" operation
    And I confirm mass edit
    And I wait for the "publish_product" job to finish
    When I am on the published products page
    Then the grid should contain 5 elements
