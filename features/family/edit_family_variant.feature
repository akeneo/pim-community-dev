@javascript
Feature: show family variant
  In order to provide accurate information about a family
  As an administrator
  I need to be able to show a family variant in a family

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  # Remove attribute from attribute set => go common attributes => show confirmation message
  Scenario: Successfully edit a family variant's attribute sets by removing an attribute
    Given the product "1111111270" should have the following values:
      | weight | 800.0000 GRAM |
    And the product model "PLAIN" should not have the following values "weight"
    And I am on the "Clothing" family page
    And I visit the "Variants" tab
    When I click on the "Clothing by color and size" row
    Then I should see the text "You can distribute the attributes of the family between the common attributes and specific attributes for a variant level. To distribute the attributes, you can search in the common attributes and you can drag & drop an attribute or an attribute group in the variant attributes of a level."
    When I remove the "Weight" attribute from the level 2
    Then the attribute "Weight" should be on the attributes level 0
    When I save the family variant

  # Scenario: buffer
  #   Then I should see the text "You have removed some attributes from variant attributes, the values of these attributes will be deleted for the products with variants on this family variant. Are you sure you want to confirm your changes?"
  #   When I press the "Ok" button in the popin
  #   Then there should be the following family variants:
  #     | code                            | family   | label-en_US                        | variant-axes_1 | variant-axes_2 | variant-attributes_1                         | variant-attributes_2 |
  #     | another_clothing_color_and_size | clothing | Clothing variant by color and size | color          | size           | color,name,image,variation_image,composition | size,ean,sku         |
  #   And I wait for the "udpate_family_variant" job to finnish
  #   Then the variant product "1111111270" should have the following values:
  #     | attribute | value |
  #     | weight    |       |
  #   Then there should be the following root product model:
  #     | code  | weight |
  #     | PLAIN |        |

    # Cannot move identifier attributes from product variant level
    # Cannot remove identifier attributes from product variant level
    # Move attribute down a level => keeps value
    # Move attribute down two levels => keep value
    # Move attribute up a level => remove value
    # Move attribute up two levels => remove value
    # Move attribute attribute group => remove value
    # Cannot edit family variant because of permissions.
    # Search in common attributes
    # Versionning family variants
