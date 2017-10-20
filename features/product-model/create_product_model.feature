@javascript
Feature: Create a product model
  Background:
    Given a "catalog_modeling" catalog configuration
    And the following families:
      | code     |
      | Hats     |
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product model
    And I should see the Code, Family and family_variant fields
    And the field family_variant should be disabled

  Scenario: Create a product model with a single level variant
    Given I fill in the following information in the popin:
      | Code             | shoes_variant |
      | Choose a family  | Shoes         |
      | Choose a variant | Shoes by size |
    And I press the "Save" button
    Then I should see the flash message "Product model successfully created"
    And I should be on the product model "shoes_variant" edit page
    And I should see the text "shoes_variant"

  Scenario: Create a product model with multiple level variant
    Given I fill in the following information in the popin:
    | Code             | clothing_color_and_size    |
    | Choose a family  | Clothing                   |
    | Choose a variant | Clothing by color and size |
    And I press the "Save" button
    Then I should see the flash message "Product model successfully created"
    And I should be on the product model "clothing_color_and_size" edit page
    And I should see the text "clothing_color_and_size"

  # Scenario: Create a product model with single level variant and metric

  Scenario: Create a product model with single variant and multiple axes
    Given I fill in the following information in the popin:
    | Code             | clothing_color_size    |
    | Choose a family  | Clothing                   |
    | Choose a variant | Clothing by color/size |
    And I press the "Save" button
    Then I should see the flash message "Product model successfully created"
    And I should be on the product model "clothing_color_size" edit page
    And I should see the text "clothing_color_size"

  Scenario: Display only families with variants
    Given I press the "Choose a family" button
    Then I should see the text "Accessories"
    And I should see the text "Clothing"
    And I should see the text "Shoes"
    And I should not see the text "Hats"

  Scenario: Select only child variant of family by default
    Given I fill in the following information in the popin:
      | Choose a family  | Accessories   |
    Then I should see the text "Accessories by size"

  Scenario: Display validation error for duplicate code and missing family variant
    Given I fill in the following information in the popin:
      | Code | artemis |
    And I press the "Save" button
    Then I should see the text "The same code is already set on another product model."
    And I should see the text "The product model family variant must not be empty."

    # Scenario: Disable create button if user does not have permission to create products and product models
    # Scenario: Disable product model creation if user does not have permission
