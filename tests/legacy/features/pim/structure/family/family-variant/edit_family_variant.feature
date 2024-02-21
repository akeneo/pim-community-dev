@javascript
Feature: Edit family variant
  In order to provide accurate information about a family
  As an administrator
  I need to be able to edit a family variant in a family

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully edit a family variant's attribute sets by removing an attribute
    When the product "1111111270" should have the following values:
      | weight | 800.0000 GRAM |
    And the product model "PLAIN" should not have the following values "weight"
    And I am on the "Clothing" family page
    And I visit the "Variants" tab
    When I click on the "Clothing by color and size" row
    Then I should see the text "Drag & drop attributes to the selected variant level to have these attributes managed at the variant level."
    When I remove the "Weight" attribute from the level 2
    And I confirm the deletion
    Then the attribute "Weight" should be on the attributes level 0
    When I press the "Save" button in the popin
    Then there should be the following family variants:
      | code                | family   | label-en_US                | variant-axes_1 | variant-axes_2 | variant-attributes_1                         | variant-attributes_2 |
      | clothing_color_size | clothing | Clothing by color and size | color          | size           | color,composition,material,variation_image,variation_name | size,ean,sku         |
    And I wait for the "compute_family_variant_structure_changes" job to finish
    Then the variant product "1111111270" should not have the following values:
      | weight    |
