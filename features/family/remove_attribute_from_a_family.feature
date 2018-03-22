@javascript
Feature: Remove attribute from a family
  In order to correct myself when I have wrongly added an attribute into a family
  As an administrator
  I need to be able to remove an attribute from a family

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully remove an attribute from a family and assert its removal from product, product-model, variant-product
    Given I am on the "accessories" family page
    And I visit the "Attributes" tab
    When I remove the "material" attribute
    And I save the family
    And I wait for the "compute_completeness_of_products_family" job to finish
    And I wait for the "compute_family_variant_structure_changes" job to finish
    And I wait for the "compute_product_models_descendants" job to finish
    Then the product "1111111292" should not have the following values:
      | material |
    And the variant product "braided-hat-m" should not have the following values:
      | material |
    And the product model "model-braided-hat" should not have the following values "material"

  Scenario: Impossible to remove some attributes from a family (used as label, used as image, used as axis)
    Given I am on the "shoes" family page
    And I visit the "Attributes" tab
    And I scroll down
    When I remove the "variation_name" attribute
    Then I should see the flash message "Cannot remove attribute used as label"
    When I remove the "variation_image" attribute
    Then I should see the flash message "Cannot remove used as the main picture"
    When I remove the "size" attribute
    Then I should see the flash message "Cannot remove this attribute used as a variant axis in a family variant"
    And I should see the text "size"

  Scenario: Successfully remove an attribute from a family removes it from the family variants.
    Given I am on the "shoes" family page
    And I visit the "Variants" tab
    When I click on the "Shoes by size and color" row
    Then I should see the text "EU Shoes Size"
    Then I should see the text "Weight"
    And I should see the text "Variation picture"
    And I should see the text "Model picture"
    When I press the cancel button in the popin
    And I visit the "Attributes" tab
    And I remove the "weight" attribute
    And I remove the "image" attribute
    And I save the family
    And I visit the "Variants" tab
    And I click on the "Shoes by size and color" row
    Then I should see the text "EU Shoes Size"
    And I should not see the text "Weight"
    And I should see the text "Variation picture"
    And I should not see the text "Model picture"
