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
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully add a sub product model with one axis to a root product model
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Color"
    When I fill in the following child information:
      | Code (required)      | apollon_black |
      | Color (variant axis) | Black         |
    And I confirm the child creation
    Then I should be on the product model "apollon_black" edit page

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
      | code                     | family   | label-en_US                | variant-axes_1                                 | variant-axes_2 | variant-attributes_1                                                             |
      | five_axes_family_variant | clothing | Clothing by color and size | color,material,weight,handmade,reference_color | size           | color,material,weight,handmade,reference_color,image,variation_image,composition |
    And the following root product model:
      | code       | parent | family_variant           |
      | root_model |        | five_axes_family_variant |
    When I am on the "root_model" product model page
    And I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Color, Handmade, Material, Reference color, Weight"
    When I fill in the following child information:
      | Code (required)                | model_with_five_axes |
      | Color (variant axis)           | Blue                 |
      | Material (variant axis)        | leather              |
      | Reference color (variant axis) | Red                  |
      | Weight (variant axis)          | 800 GRAM             |
      | Handmade (variant axis)        | Yes                  |
    And I confirm the child creation
    Then I should be on the product model "model_with_five_axes" edit page

  Scenario: Successfully add a new sub product model when I already am on a sub product product model
    Given I am on the "apollon_blue" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Color"
    When I fill in the following child information:
      | Code (required)      | apollon_black |
      | Color (variant axis) | Black         |
    And I confirm the child creation
    Then I should be on the product model "apollon_black" edit page

  Scenario: Successfully add a sub product model when I am on a variant product
    Given I am on the "1111111121" product page
    When I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Color"
    When I fill in the following child information:
      | Code (required)      | apollon_black |
      | Color (variant axis) | Black         |
    And I confirm the child creation
    Then I should be on the product model "apollon_black" edit page

  @critical
  Scenario: Successfully add a variant product to a root product model
    Given I am on the "amor" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Color, Size"
    When I fill in the following child information:
      | SKU (required)       | amor_black_xl |
      | Color (variant axis) | Black         |
      | Size (variant axis)  | XL            |
    And I confirm the child creation
    Then I should be on the product "amor_black_xl" edit page

  @critical
  Scenario: Successfully add a variant product to a sub product model
    Given I am on the "apollon_blue" product model page
    When I open the variant navigation children selector for level 2
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Size"
    When I fill in the following child information:
      | SKU (required)      | apollon_blue_xl |
      | Size (variant axis) | XL              |
    And I confirm the child creation
    Then I should be on the product "apollon_blue_xl" edit page

  Scenario: Successfully add a new variant product when I already am on a variant product
    When I am on the "1111111121" product page
    And I open the variant navigation children selector for level 2
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Size"
    When I fill in the following child information:
      | SKU (required)      | apollon_blue_xl |
      | Size (variant axis) | XL              |
    And I confirm the child creation
    Then I should be on the product "apollon_blue_xl" edit page

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
      | code                     | family   | label-en_US                          | variant-axes_1                                | variant-attributes_1                                                                    |
      | five_axes_family_variant | clothing | Clothing by reference color and size | reference_color,size,material,weight,handmade | reference_color,size,material,weight,handmade,image,variation_image,composition,ean,sku |
    And the following root product model:
      | code       | parent | family_variant           |
      | root_model |        | five_axes_family_variant |
    When I am on the "root_model" product model page
    And I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Handmade, Material, Reference color, Size, Weight"
    When I fill in the following child information:
      | SKU (required)                 | tshirt_with_five_axes |
      | Material (variant axis)        | leather               |
      | Reference color (variant axis) | Red                   |
      | Size (variant axis)            | XL                    |
      | Weight (variant axis)          | 800 GRAM              |
      | Handmade (variant axis)        | Yes                   |
    And I confirm the child creation
    Then I should be on the product "tshirt_with_five_axes" edit page

  Scenario: I cannot add a sub product model without code
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Color"
    When I fill in the following child information:
      | Color (variant axis) | Black |
    And I confirm the child creation
    Then I should see the text "The product model code must not be empty."

  Scenario: I cannot add a variant product without identifier
    Given I am on the "apollon_blue" product model page
    When I open the variant navigation children selector for level 2
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new size"
    When I fill in the following child information:
      | Size (variant axis) | XL |
    And I confirm the child creation
    Then I should see the text "This value should not be blank."

  Scenario: I cannot add a sub product model without axis value
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new color"
    When I fill in the following child information:
      | Code (required) | apollon_black |
    And I confirm the child creation
    Then I should see the text "Attribute \"color\" cannot be empty, as it is defined as an axis for this entity"

  Scenario: I cannot add a variant product without axis value
    Given I am on the "apollon_blue" product model page
    When I open the variant navigation children selector for level 2
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new size"
    When I fill in the following child information:
      | SKU (required) | apollon_black_xl |
    And I confirm the child creation
    Then I should see the text "Attribute \"size\" cannot be empty, as it is defined as an axis for this entity"

  Scenario: I cannot add a sub product model with an already existing axis value combination
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new color"
    When I fill in the following child information:
      | Code (required)      | apollon_new_blue |
      | Color (variant axis) | Blue             |
    And I confirm the child creation
    Then I should see the text "Cannot set value \"[blue]\" for the attribute axis \"color\" on product model \"apollon_new_blue\", as the product model \"apollon_blue\" already has this value"

  Scenario: I cannot add a variant product with an already existing axis value combination
    Given I am on the "amor" product model page
    When I open the variant navigation children selector for level 1
    And I press the "Add new" button and wait for modal
    Then I should see the text "Add a new Color, Size"
    When I fill in the following child information:
      | SKU (required)       | apollon_new_blue_m |
      | Color (variant axis) | Blue               |
      | Size (variant axis)  | M                  |
    And I confirm the child creation
    Then I should see the text "Cannot set value \"[blue],[m]\" for the attribute axis \"color,size\" on variant product \"apollon_new_blue_m\", as the variant product \"1111111113\" already has this value"
