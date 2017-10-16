@javascript
Feature: Create a root product model
  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Create a root product model with single variant
    Given I create a product model
    And I should see the Code, Family, Variant fields
    And the field Variant should be disabled
    When I fill in the following information in the popin:
      | Code | clothing_variant |
    Then I select the family "Clothing"
    Then I select the variant "shoes_size"
    And I press the "Confirm" button
    Then I should see the flash message "Product model successfully created"

    # Scenario: Create a root product model with multiple variants
    #   clothing_color_size
    #
    # Scenario: Create a root product model with single variant and metric
    #   Create family variant with metric
    #
    # Scenario: Create a root product model with single variant and multiple axes
    #   clothing_colorsize
    #
    # Scenario: Display only families that have at least one variant
    # Scenario: Enable variant field only when family is selected
    # Scenario: Select only child variant of family by default
    # Scenario: Successfully create product model with code and empty common attributes
    #   Display "Product model successfully created"
    # Scenario: Disable product model creation if user does not have permission
