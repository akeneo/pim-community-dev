@javascript
Feature: Add children to product model
  In order to enrich the catalog
  As a regular user
  I need to be able to add children to a product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully add a sub product model with one axis to a root product model
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press "Create new"
    Then I should see the text "Add a color"
    When I fill in "code" with "apollon_black"
    And I fill in "color" with "black"
    And I confirm the child creation
    Then I should see the text "Product model successfully added to the product model"
    And I should be on the product model "apollon_black" edit page

  Scenario: Successfully add a sub product model with many axes to a root product model
    Given the following attribute:
      | code     | label-en_US | type                | group |
      | handmade | Handmade    | pim_catalog_boolean | other |
    And I am on the "clothing" family page
    And I visit the "Attributes" tab
    And I add available attributes Handmade
    And I save the family
    And I should not see the text "There are unsaved changes."
    And the following family variant:
      | code                     | family   | label-en_US                | variant-axes_1                 | variant-axes_2 | variant-attributes_1              |
      | four_axes_family_variant | clothing | Clothing by color and size | color,material,weight,handmade | size           | image,variation_image,composition |
    And the following root product model:
      | code       | parent | family_variant           |
      | root_model |        | four_axes_family_variant |
    When I am on the "root_model" product model page
    And I open the variant navigation children selector for level 1
    And I press "Create new"
    Then I should see the text "Add a color, handmade, material, weight"
    When I fill in "code" with "model_with_four_axes"
    And I fill in "color" with "black"
    And I fill in "handmade" with ""
    And I fill in "material" with ""
    And I fill in "weight" with ""
    And I confirm the child creation
    Then I should see the text "Product model successfully added to the product model"
    And I should be on the product model "model_with_four_axes" edit page

  Scenario: Successfully add a new sub product model when I already am on a sub product product model

  Scenario: Successfully add a sub product model when I am on a variant product

  Scenario: Successfully add a variant product to a root product model
    # shoes on the variant size (1 level)

  Scenario: Successfully add a variant product to a sub product model
    # t-shirts on the variant size (2 levels)

  Scenario: Successfully add a new variant product when I already am on a variant product

  Scenario: Successfully add a variant product to a product model with metric as variant axis
    # tvs on the variant display diagonal (1 levels with metric)

  Scenario: Successfully add a variant product with many axes to a root product model
    Given the following attribute:
      | code     | label-en_US | type                | group |
      | handmade | Handmade    | pim_catalog_boolean | other |
    And I am on the "clothing" family page
    And I visit the "Attributes" tab
    And I add available attributes Handmade
    And I save the family
    And I should not see the text "There are unsaved changes."
    And the following family variant:
      | code                     | family   | label-en_US                | variant-axes_1                      | variant-attributes_1                      |
      | five_axes_family_variant | clothing | Clothing by color and size | color,size,material,weight,handmade | image,variation_image,composition,ean,sku |
    And the following root product model:
      | code       | parent | family_variant           |
      | root_model |        | five_axes_family_variant |
    When I am on the "root_model" product model page
    And I open the variant navigation children selector for level 1
    And I press "Create new"
    Then I should see the text "Add a color, handmade, material, size, weight"
    When I fill in "code" with "tshirt_with_five_axes"
    And I fill in "color" with "black"
    And I fill in "handmade" with ""
    And I fill in "material" with ""
    And I fill in "size" with "xl"
    And I fill in "weight" with ""
    And I confirm the child creation
    Then I should see the text "Variant product successfully added to the product model"
    And I should be on the product "tshirt_with_five_axes" edit page

  Scenario: I cannot add a variant product to a root product model when there is two levels of variations
    When I am on the "apollon" product model page
    Then I should not see the variant navigation children selector for level 2

  Scenario: I cannot add a sub product model without code

  Scenario: I cannot add a variant product without code

  Scenario: I cannot add a sub product model without axis value

  Scenario: I cannot add a variant product without axis value

  Scenario: I cannot add a sub product model with an already existing axis value combination

  Scenario: I cannot add a variant product with an already existing axis value combination
