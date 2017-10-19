@javascript
Feature: Create a product model
  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Create a product model with single variant
    Given I create a product model
    And I should see the Code, Family and family_variant fields
    And the field family_variant should be disabled
    When I fill in the following information in the popin:
      | Code | shoes_variant             |
      | Choose a family | Shoes          |
      | Choose a variant | Shoes by size  |
    And I press the "Save" button
    Then I should see the flash message "Product model successfully created"

    # Scenario: Create a product model with multiple variants
    #   clothing_color_size
    #
    # Scenario: Create a product model with single variant and metric
    #   Create family variant with metric
    #
    # Scenario: Create a product model with single variant and multiple axes
    #   clothing_colorsize
    #
    # Scenario: Display only families that have at least one variant
    # Scenario: Enable variant field only when family is selected
    # Scenario: Select only child variant of family by default
    # Scenario: Successfully create product model with code and empty common attributes
    #   Display "Product model successfully created"
    # Scenario: Disable product model creation if user does not have permission
