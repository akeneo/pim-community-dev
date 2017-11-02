@javascript
Feature: Add children to product model
  In order to enrich the catalog
  As a regular user
  I need to be able to add children to a product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And the following attributes:
      | code            | label-en_US     | localizable | scopable | type                            | reference_data_name | group |
      | reference_color | Reference color | 0           | 0        | pim_reference_data_simpleselect | color               | other |
    And the following "reference_color" attribute reference data: Red, Blue and Green
    And I am logged in as "Julia"

  Scenario: Successfully add a sub product model with one axis to a root product model
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Color"
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
    And I add available attributes Reference color
    And I save the family
    And I should not see the text "There are unsaved changes."
    And the following family variant:
      | code                     | family   | label-en_US                | variant-axes_1                                 | variant-axes_2 | variant-attributes_1              |
      | five_axes_family_variant | clothing | Clothing by color and size | color,material,weight,handmade,reference_color | size           | image,variation_image,composition |
    And the following root product model:
      | code       | parent | family_variant           |
      | root_model |        | five_axes_family_variant |
    When I am on the "root_model" product model page
    And I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Color, Handmade, Material, Reference color, Weight"
    And I fill in the following child values:
      | Code            | model_with_five_axes |
      | Color           | Blue                 |
      | Material        | leather              |
      | Reference color | Red                  |
      | Weight          | 800 GRAM             |
      | Handmade        | Yes                  |
    And I confirm the child creation
    Then I should see the text "Product model successfully added to the product model"
    And I should be on the product model "model_with_five_axes" edit page

  Scenario: Successfully add a new sub product model when I already am on a sub product product model
    Given I am on the "apollon_blue" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Color"
    When I fill in "code" with "apollon_black"
    And I fill in "color" with "black"
    And I confirm the child creation
    Then I should see the text "Product model successfully added to the product model"
    And I should be on the product model "apollon_black" edit page

  Scenario: Successfully add a sub product model when I am on a variant product
    Given I am on the "1111111121" product page
    When I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Color"
    When I fill in "code" with "apollon_black"
    And I fill in "color" with "black"
    And I confirm the child creation
    Then I should see the text "Product model successfully added to the product model"
    And I should be on the product model "apollon_black" edit page

  Scenario: Successfully add a variant product to a root product model
    Given I am on the "amor" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Color, Size"
    When I fill in "code" with "amor_black_xl"
    And I fill in "color" with "black"
    And I fill in "size" with "xl"
    And I confirm the child creation
    Then I should see the text "Variant product successfully added to the product model"
    And I should be on the product "amor_black_xl" edit page

  Scenario: Successfully add a variant product to a sub product model
    Given I am on the "apollon_blue" product model page
    When I open the variant navigation children selector for level 2
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Size"
    When I fill in "code" with "apollon_blue_xl"
    And I fill in "size" with "xl"
    And I confirm the child creation
    Then I should see the text "Variant product successfully added to the product model"
    And I should be on the product "apollon_blue_xl" edit page

  Scenario: Successfully add a new variant product when I already am on a variant product
    When I am on the "1111111121" product model page
    And I open the variant navigation children selector for level 2
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Size"
    When I fill in "code" with "apollon_blue_xl"
    And I fill in "size" with "xl"
    And I confirm the child creation
    Then I should see the text "Variant product successfully added to the product model"
    And I should be on the product "apollon_blue_xl" edit page

  Scenario: Successfully add a variant product with many axes to a root product model
    Given the following attribute:
      | code     | label-en_US | type                | group |
      | handmade | Handmade    | pim_catalog_boolean | other |
    And I am on the "clothing" family page
    And I visit the "Attributes" tab
    And I add available attributes Handmade
    And I add available attributes Reference color
    And I save the family
    And I should not see the text "There are unsaved changes."
    And the following family variant:
      | code                     | family   | label-en_US                          | variant-axes_1                                | variant-attributes_1                      |
      | five_axes_family_variant | clothing | Clothing by reference color and size | reference_color,size,material,weight,handmade | image,variation_image,composition,ean,sku |
    And the following root product model:
      | code       | parent | family_variant           |
      | root_model |        | five_axes_family_variant |
    When I am on the "root_model" product model page
    And I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Handmade, Material, Reference color, Size, Weight"
    And I fill in the following child values:
      | SKU             | tshirt_with_five_axes |
      | Material        | leather               |
      | Reference color | Red                   |
      | Size            | XL                    |
      | Weight          | 800 GRAM              |
      | Handmade        | Yes                   |
    And I confirm the child creation
    Then I should see the text "Variant product successfully added to the product model"
    And I should be on the product "tshirt_with_five_axes" edit page

  Scenario: I cannot add a sub product model without code
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Color"
    And I fill in "color" with "black"
    And I confirm the child creation
    Then I should see a validation error "This value should not be blank."

  Scenario: I cannot add a variant product without code
    Given I am on the "apollon_blue" product model page
    When I open the variant navigation children selector for level 2
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a size"
    And I fill in "size" with "xl"
    And I confirm the child creation
    Then I should see a validation error "This value should not be blank."

  Scenario: I cannot add a sub product model without axis value
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a color"
    When I fill in "code" with "apollon_black"
    And I confirm the child creation
    Then I should see a validation error "This value should not be blank."

  Scenario: I cannot add a variant product without axis value
    Given I am on the "apollon_blue" product model page
    When I open the variant navigation children selector for level 2
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a size"
    When I fill in "code" with "apollon_black_xl"
    And I confirm the child creation
    Then I should see a validation error "This value should not be blank."

  Scenario: I cannot add a sub product model with an already existing axis value combination
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a color"
    When I fill in "code" with "apollon_new_blue"
    And I fill in "color" with "blue"
    And I confirm the child creation
    Then I should see a validation error "A variant blue already exists for this product model."

  Scenario: I cannot add a variant product with an already existing axis value combination
    Given I am on the "amor" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Create new" button and wait for modal
    Then I should see the text "Add a Color, Size"
    And I fill in "color" with "blue"
    And I fill in "size" with "xl"
    And I confirm the child creation
    Then I should see a validation error "A variant blue, xl already exists for this product model."
