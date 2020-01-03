@javascript
Feature: Apply a add to products to existing product model
  In order to link my products to product model
  As a product manager
  I need to be able to select products and link them to an existing product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully display leaf product models
    Given I am on the products grid
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
    Given I am on the products grid
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

  @critical
  Scenario: Successfully adds products to product model
    Given I am on the "1111111171" product page
    When I visit the "Product" group
    And I fill in the following information:
      | Size | S |
    And I save the product
    And I am on the products grid
    And I select rows 1111111171
    And I press the "Bulk actions" bottom button
    And I choose the "Add to an existing product model" operation
    And I fill in the following information:
      | Family (required)        | Accessories |
      | Product model (required) | Braided hat |
    When I confirm mass edit
    And I wait for the "add_to_existing_product_model" job to finish
    Then I should see the text "COMPLETED"
    And I should see the text "Processed 1"
    And the parent of the product "1111111171" should be "model-braided-hat"
