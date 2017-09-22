# TODO: PIM-6357 - Scenario should be removed once mass edits work for product models.
@javascript
Feature: Apply a mass action on products only (and not product models)
  In order to modify my catalog
  As a product manager
  I need to be able to select products and product models at the same time in the grid and apply mass-edit on them

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Apply a mass action on products and product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I select rows model-tshirt-divided-crimson-red, running-shoes-m-crimson-red and tshirt-unique-size-crimson-red
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Composition attribute
    And I change the "Composition" to "my composition"
    When I move to the confirm page
    Then I should see the text "You are about to update 3 products with the following information, please confirm."

  Scenario: Mass edits common attributes of only products within a selection of products and product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I select rows model-tshirt-divided-crimson-red, running-shoes-m-crimson-red and tshirt-unique-size-crimson-red
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    And I display the Composition attribute
    And I change the "Composition" to "My composition"
    When I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    When I go on the last executed job resume of "edit_common_attributes"
    Then I should see the text "COMPLETED"
    And I should see the text "processed 2"
    And I should see the text "skipped 1"
    And I should see the text "Bulk actions do not support Product models entities yet."
    And attribute composition of "tshirt-unique-size-crimson-red" should be "My composition"
    And attribute composition of "running-shoes-m-crimson-red" should be "My composition"

  Scenario: Mass edits family of only products within a selection of products and product models
    Given I show the filter "color"
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
    And I should see the text "skipped 1"
    And I should see the text "Skipped products 1"
    And I should see the text "The variant product family must be the same than its parent: tshirt-unique-size-navy-blue"
    And I should see the text "Bulk actions do not support Product models entities yet."
    And the family of product "watch" should be "shoes"
    And the family of product "tshirt-unique-size-crimson-red" should be "clothing"
    And the family of product model "model-tshirt-divided-crimson-red" should be "clothing"

  Scenario: Mass edits status of only products within a selection of products and product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Navy blue"
    And I select rows watch, tshirt-unique-size-navy-blue and model-tshirt-divided-navy-blue
    And I press the "Bulk actions" button
    And I choose the "Change status" operation
    And I disable the products
    And I wait for the "update_product_value" job to finish
    When I go on the last executed job resume of "update_product_value"
    Then I should see the text "COMPLETED"
    And I should see the text "processed 2"
    And I should see the text "skipped 1"
    And I should see the text "Bulk actions do not support Product models entities yet."
    And product "watch" should be disabled
    And product "tshirt-unique-size-navy-blue" should be disabled

  Scenario: Mass edits groups of only products within a selection of products and product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Navy blue"
    And I select rows watch, tshirt-unique-size-navy-blue and model-tshirt-divided-navy-blue
    And I press the "Bulk actions" button
    And I choose the "Add to groups" operation
    And I check "Related"
    When I confirm mass edit
    And I wait for the "add_product_value" job to finish
    When I go on the last executed job resume of "add_product_value"
    Then I should see the text "COMPLETED"
    And I should see the text "processed 2"
    And I should see the text "skipped 1"
    And I should see the text "Bulk actions do not support Product models entities yet."
    Then "related" group should contain "watch, tshirt-unique-size-navy-blue"

  Scenario: Mass edits add categories of only products within a selection of products and product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Navy blue"
    And I select rows watch, tshirt-unique-size-navy-blue and model-tshirt-divided-navy-blue
    And I press the "Bulk actions" button
    And I choose the "Add to categories" operation
    And I move on to the choose step
    And I choose the "Add to categories" operation
    And I select the "Master" tree
    And I expand the "master" category
    And I press the "Women" button
    And I confirm mass edit
    And I wait for the "add_product_value" job to finish
    When I go on the last executed job resume of "add_product_value"
    Then I should see the text "COMPLETED"
    And I should see the text "processed 2"
    And I should see the text "skipped 1"
    And I should see the text "Bulk actions do not support Product models entities yet."
    When I am on the products grid
    And I open the category tree
    Then I should be able to use the following filters:
      | filter   | operator | value        | result                              |
      | category |          | master_women | watch, tshirt-unique-size-navy-blue |

  Scenario: Mass edits move categories of only products within a selection of products and product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Navy blue"
    And I select rows watch, tshirt-unique-size-navy-blue and model-tshirt-divided-navy-blue
    And I press the "Bulk actions" button
    And I choose the "Move between categories" operation
    And I move on to the choose step
    And I choose the "Move between categories" operation
    And I select the "Master" tree
    And I expand the "master" category
    And I press the "Women" button
    And I confirm mass edit
    And I wait for the "update_product_value" job to finish
    When I go on the last executed job resume of "update_product_value"
    Then I should see the text "COMPLETED"
    And I should see the text "processed 2"
    And I should see the text "skipped 1"
    And I should see the text "Bulk actions do not support Product models entities yet."
    When I am on the products grid
    And I open the category tree
    Then I should be able to use the following filters:
      | filter   | operator | value        | result                              |
      | category |          | master_women | watch, tshirt-unique-size-navy-blue |

  Scenario: Mass edits remove categories of only products within a selection of products and product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Navy blue"
    And I select rows watch, tshirt-unique-size-navy-blue and model-tshirt-divided-navy-blue
    And I press the "Bulk actions" button
    And I choose the "Remove from categories" operation
    And I move on to the choose step
    And I choose the "Remove from categories" operation
    And I select the "Master" tree
    And I expand the "master" category
    And I expand the "master_men" category
    And I press the "T-shirts" button
    And I select the "Suppliers" tree
    And I expand the "suppliers" category
    And I press the "Zaro" button
    And I confirm mass edit
    And I wait for the "remove_product_value" job to finish
    When I go on the last executed job resume of "remove_product_value"
    Then I should see the text "COMPLETED"
    And I should see the text "processed 2"
    And I should see the text "skipped 1"
    And I should see the text "Bulk actions do not support Product models entities yet."
    And the product "watch" should not have any category
    And the product "tshirt-unique-size-navy-blue" should not have any category
