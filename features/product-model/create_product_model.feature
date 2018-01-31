@javascript
Feature: Create a product model
  In order to add a non-imported product model
  As a product manager
  I need to be able to manually create a product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Create a product model with a single level variant
    When I create a product model
    And I should see the Code, Family and Variant fields
    And the field Variant should be disabled
    When I fill in the following information in the popin:
      | Code    | shoes_variant |
      | Family  | Shoes         |
      | Variant | Shoes by size |
    And I press the "Save" button
    And I should be on the product model "shoes_variant" edit page
    And I should see the text "shoes_variant"

  Scenario: Create a product model with multiple level variant
    When I create a product model
    And I should see the Code, Family and Variant fields
    And the field Variant should be disabled
    When I fill in the following information in the popin:
      | Code    | clothing_color_and_size    |
      | Family  | Clothing                   |
      | Variant | Clothing by color and size |
    And I press the "Save" button
    And I should be on the product model "clothing_color_and_size" edit page
    And I should see the text "clothing_color_and_size"

  Scenario: Create a product model with single level variant and metric
    When I create a product model
    And the following attributes:
      | code             | type               | group | metric_family | default_metric_unit | decimals_allowed | negative_allowed |
      | display_diagonal | pim_catalog_metric | other | Length        | CENTIMETER          | 0                | 0                |
    And the following families:
      | code     | attributes             | label-en_US |
      | led_tvs  | name,display_diagonal  | LED TVs     |
    And the following family variants:
      | code  | family  | variant-axes_1    | variant-attributes_1 | label-en_US |
      | tv    | led_tvs | display_diagonal  | name                 | LED TV      |
    And I should see the Code, Family and Variant fields
    And the field Variant should be disabled
    When I fill in the following information in the popin:
      | Code   | tv_display_diagonal |
      | Family | LED TVs             |
    Then I should see the text "LED TVs"
    And I should see the text "LED TV"
    And I press the "Save" button
    And I should be on the product model "tv_display_diagonal" edit page
    And I should see the text "tv_display_diagonal"

  Scenario: Create a product model with single variant and multiple axes
    When I create a product model
    And I should see the Code, Family and Variant fields
    And the field Variant should be disabled
    When I fill in the following information in the popin:
      | Code    | clothing_color_size    |
      | Family  | Clothing               |
      | Variant | Clothing by color/size |
    And I press the "Save" button
    And I should be on the product model "clothing_color_size" edit page
    And I should see the text "clothing_color_size"

  Scenario: Display only families with variants
    When I create a product model
    And the following families:
      | code |
      | hats |
    And I should see the Code, Family and Variant fields
    And the field Variant should be disabled
    When I press the "Choose a family" button
    Then I should see the text "Accessories"
    And I should see the text "Clothing"
    And I should see the text "Shoes"
    And I should not see the text "Hats"

  Scenario: Select only child variant of family by default
    When I create a product model
    And I should see the Code, Family and Variant fields
    And the field Variant should be disabled
    When I fill in the following information in the popin:
      | Code   | accessories_size |
      | Family | Accessories      |
    Then I should see the text "Accessories by size"
    And I press the "Save" button
    And I should be on the product model "accessories_size" edit page
    And I should see the text "accessories_size"

  Scenario: Display validation error for duplicate code
    When I create a product model
    And I should see the Code, Family and Variant fields
    And the field Variant should be disabled
    When I fill in the following information in the popin:
      | Code | artemis |
    And I press the "Save" button
    Then I should see the text "The same code is already set on another product model."

  Scenario: Display validation error for missing family variant
    When I create a product model
    And I should see the Code, Family and Variant fields
    And the field Variant should be disabled
    When I fill in the following information in the popin:
      | Code | artemiz |
    And I press the "Save" button
    And I should see the text "The product model family variant must not be empty."

  Scenario: Disable create button if user does not have permission to create products and product models
    When I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Create a product model
    And I revoke rights to resource Create a product
    And I save the role
    And I am on the products grid
    Then I refresh current page
    And I should not see the "Create product and product models" button

  Scenario: Disable product model creation if user does not have permission
    When I am on the "Catalog manager" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Create a product model
    And I save the role
    And I am on the products grid
    And I press the "Create product and product models" button
    Then I should see the SKU and Family fields
