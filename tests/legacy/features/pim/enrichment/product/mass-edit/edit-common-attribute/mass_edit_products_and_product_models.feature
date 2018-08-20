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
    And I am on the products page

  Scenario: Apply a mass action on products and product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I select rows model-tshirt-divided-crimson-red, running-shoes-m-crimson-red and tshirt-unique-size-crimson-red
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Composition attribute
    And I change the "Composition" to "my composition"
    When I move to the confirm page
    Then I should see the text "You are about to update 6 products with the following information, please confirm."
