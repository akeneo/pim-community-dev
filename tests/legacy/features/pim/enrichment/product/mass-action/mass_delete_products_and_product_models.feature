@javascript
Feature: Delete many products and product models at once
  In order to easily manage catalog
  As a product manager
  I need to be able to remove many products and product models at once

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Successfully remove many products and product models and see all correct information
    Given I sort by "ID" value ascending
    And I select rows Bag, Belt, amor and caelus
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I am on the products grid
    And I should not see products bag belt amor and caelus
    And I should have 1 new notification
    And I should see notification:
      | type    | message                                   |
      | success | Mass delete Mass delete products finished |
    When I go on the last executed job resume of "delete_products_and_product_models"
    Then I should see the text "COMPLETED"
    And I should see the text "Deleted products 11"
    And I should see the text "Deleted product models 4"

  Scenario: Successfully mass delete visible products and product models
    Given I sort by "ID" value ascending
    And I select rows Bag
    And I select all visible entities
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I go on the last executed job resume of "delete_products_and_product_models"
    Then I should see the text "COMPLETED"
    And I should see the text "Deleted products 117"
    And I should see the text "Deleted product models 37"

  Scenario: Successfully mass delete all products and product models
    Given I select rows Bag
    And I select all entities
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I am on the products grid
    Then the grid should contain 0 elements
