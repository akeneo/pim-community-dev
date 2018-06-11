@javascript
Feature: Add products and variant products to many groups at once via a form
  In order to easily organize products into groups
  As a product manager
  I need to be able to add products to many groups at once via a form

  Scenario: Add products to a related group
    Given the "footwear" catalog configuration
    And the following products:
      | sku          |
      | kickers      |
      | hiking_shoes |
      | moon_boots   |
    And I am logged in as "Julia"
    And I am on the products grid
    Given I select rows kickers, hiking_shoes and moon_boots
    And I press the "Bulk actions" button
    And I choose the "Add to groups" operation
    And I change the "Groups" to "Similar boots"
    When I confirm mass edit
    And I wait for the "add_to_group" job to finish
    Then "similar_boots" group should contain "kickers, hiking_shoes and moon_boots"

  Scenario: Add variant products to a related group by selecting a product model
    Given a "default" catalog configuration
    And the following product groups:
      | code    | label-en_US | type   |
      | bageneo | Bageneo     | X_SELL |
    And the following attributes:
      | code        | label-en_US | type                         | localizable | scopable | group | decimals_allowed |
      | color       | Color       | pim_catalog_simpleselect     | 0           | 0        | other |                  |
      | description | Description | pim_catalog_textarea         | 1           | 1        | other |                  |
      | name        | Name        | pim_catalog_text             | 1           | 0        | other |                  |
      | size        | Size        | pim_catalog_simpleselect     | 0           | 0        | other |                  |
    And the following "color" attribute options: red, yellow, black and white
    And the following "size" attribute options: s, m, l, xl
    And the following family:
      | code | requirements-ecommerce | requirements-mobile | attributes                      |
      | bags | sku                    | sku                 | color,description,name,size,sku |
    And the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1       |
      | bag_unisize    | bags   | size           | size,color,description,sku |
    And the following root product models:
      | code      | categories | family_variant | name-en_US  | color |
      | bag_white | default    | bag_unisize    | Bag atelle  | white |
      | bag_red   | default    | bag_unisize    | Bag arderue | red   |
    And the following products:
      | sku             | parent    | family | categories | size |
      | bag_white_large | bag_white | bags   | default    | l    |
      | bag_white_small | bag_white | bags   | default    | s    |
      | bag_red_small   | bag_red   | bags   | default    | s    |
      | bag_red_large   | bag_red   | bags   | default    | l    |
    And I am logged in as "Julia"
    And I am on the products grid
    When I select rows bag_white and bag_red
    And I press the "Bulk actions" button
    And I choose the "Add to groups" operation
    And I change the "Groups" to "Bageneo"
    And I confirm mass edit
    And I wait for the "add_to_group" job to finish
    Then "bageneo" group should contain "bag_white_large, bag_white_small, bag_red_small and bag_red_large"
