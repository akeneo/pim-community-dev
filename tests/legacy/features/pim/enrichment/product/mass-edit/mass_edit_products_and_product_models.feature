# TODO: PIM-6357 - Scenario should be removed once mass edits work for product models.
@javascript
Feature: Apply a mass action on products only (and not product models)
  In order to modify my catalog
  As a product manager
  I need to be able to select products and product models at the same time in the grid and apply mass-edit on them

  Background:
    Given a "catalog_modeling" catalog configuration
    And the following categories:
      | code         | label_en_US  | parent  |
      | long_sleeves | Long sleeves | tshirts |
      | seasons      | Seasons      | tshirts |
      | summer       | Summer       | seasons |
      | spring       | Spring       | seasons |
    And the following root product models:
      | code      | family_variant      | categories |
      | model-nin | clothing_color_size | tshirts    |
    And the following sub product models:
      | code            | parent    | family_variant      | categories                  | color |
      | model-nin-black | model-nin | clothing_color_size | summer,spring,supplier_zaro | black |
    And the following products:
      | sku                  | family      | categories                        | color | size |
      | cult-of-luna-black-m | clothing    | long_sleeves,summer,supplier_zaro | black | m    |
      | another-watch        | accessories | supplier_zaro                     | black |      |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Mass edits family of only products within a selection of products and product models
    Given I type "col" in the manage filter input
    And I show the filter "color"
    And I filter by "color" with operator "in list" and value "Navy blue"
    And I select rows watch, tshirt-unique-size-navy-blue and model-tshirt-divided-navy-blue
    And I press the "Bulk actions" button
    And I choose the "Change family" operation
    And I change the Family to "Shoes"
    And I confirm mass edit
    And I wait for the "update_product_value" job to finish
    When I go on the last executed job resume of "update_product_value"
    Then I should see the text "COMPLETED"
    And I should see the text "processed 1"
    And I should see the text "Skipped products 5"
    And I should see the text "family: The variant product family must be the same than its parent: tshirt-divided-navy-blue-xxs"
    And I should see the text "family: The variant product family must be the same than its parent: tshirt-divided-navy-blue-m"
    And I should see the text "family: The variant product family must be the same than its parent: tshirt-divided-navy-blue-l"
    And I should see the text "family: The variant product family must be the same than its parent: tshirt-divided-navy-blue-xxxl"
    And I should see the text "family: The variant product family must be the same than its parent: tshirt-unique-size-navy-blue"
    And the family of product "watch" should be "shoes"
    And the family of product "tshirt-unique-size-crimson-red" should be "clothing"
    And the family of product model "model-tshirt-divided-crimson-red" should be "clothing"
