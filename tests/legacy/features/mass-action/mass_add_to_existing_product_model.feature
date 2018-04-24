@javascript
Feature: Apply a add to products to existing product model
  In order to link my products to product model
  As a product manager
  I need to be able to select products and link them to an existing product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: It automatically selects family variant when there is only one
    Given I am on the products page
    And I select rows 1111111171
    And I press the "Bulk actions" button
    And I choose the "Add to an existing product model" operation
    And I fill in the following information:
      | Family | Accessories |
    Then I should see the text "Accessories by size"

  Scenario: Successfully display leaf product models
    Given I am on the products page
    And I select rows 1111111171
    And I press the "Bulk actions" button
    And I choose the "Add to an existing product model" operation
    And I fill in the following information:
      | Family (required)        | Clothing                   |
      | Variant (required)       | Clothing by color and size |
      | Product model (required) | Apollon blue               |
    When I move on to the next step
    Then I should see the text "Apollon blue"
    And the fields Family should be disabled

  Scenario: Successfully show validation error on family issues
    Given I am on the products page
    And I select rows 1111111171
    And I press the "Bulk actions" button
    And I choose the "Add to an existing product model" operation
    And I fill in the following information:
      | Family (required)        | Clothing                   |
      | Variant (required)       | Clothing by color and size |
      | Product model (required) | Apollon blue               |
    When I confirm mass edit
    And I wait for the "add_to_existing_product_model" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "The variant product family must be the same than its parent"

  Scenario: Successfully adds products to product model
    Given I am on the "1111111171" product page
    When I visit the "Product" group
    And I fill in the following information:
      | Size | S |
    And I save the product
    And I am on the products page
    And I select rows 1111111171
    And I press the "Bulk actions" button
    And I choose the "Add to an existing product model" operation
    And I fill in the following information:
      | Family (required)        | Accessories |
      | Product model (required) | Braided hat |
    When I confirm mass edit
    And I wait for the "add_to_existing_product_model" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Processed 1"
    And the parent of the product "1111111171" should be "model-braided-hat"
    
  Scenario: Fail to adds products to product model with null metric axis
    Given I am on the products page
    And the following attributes:
      | code                  | label-en_US           | type                     | unique | group     | decimals_allowed | negative_allowed | metric_family | default_metric_unit | useable_as_grid_filter |
      | display_diagonal      | Display diagonal      | pim_catalog_metric       | 0      | other     | 0                | 0                | Length        | INCH                | 1                      |
    And the following family:
      | code     | label-en_US | attributes                              |
      | led_tvs  | LED TVs     | display_diagonal,sku,color,weight |
    And the following family variants:
      | code                | family   | label-en_US                | variant-axes_1      | variant-attributes_1   |
      | tv_diagonal         | led_tvs  | Tv by display diagonal     | display_diagonal    | display_diagonal,sku   |
    And the following root product models:
      | code      | family_variant |
      | model-tv  | tv_diagonal    |
    And the following products:
      | sku         | family   | parent          | color | weight   |
      | 68591524    | led_tvs  |                 | black | 478 GRAM |
    And I filter by "family" with operator "in list" and value "LED TVs"
    And I select rows 68591524
    And I press the "Bulk actions" button
    And I choose the "Add to an existing product model" operation
    And I fill in the following information:
      | Family (required)        | LED TVs |
      | Product model (required) | model-tv |
    When I confirm mass edit
    And I wait for the "add_to_existing_product_model" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "attribute: Attribute \"display_diagonal\" cannot be empty"
