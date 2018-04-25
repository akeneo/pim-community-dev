@javascript
Feature: Delete many products at once that I own
  In order to secure integrity of the products catalog
  As a user
  I need to be able to mass delete only products I own

  Background:
    Given the "footwear" catalog configuration

  Scenario: Successfully mass delete a selection of products only categorized in categories I own
    Given the following products:
      | sku          | categories                 |
      | blue_sandal  | sandals                    |
      | black_sandal | sandals, summer_collection |
    And I am logged in as "Mary"
    And I am on the products grid
    And I select row blue_sandal
    And I select all visible entities
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I am on the products grid
    Then I should not see products blue_sandal and black_sandal

  Scenario: Successfully mass delete with a product not categorized
    Given the following products:
      | sku          | categories                 |
      | blue_sandal  |                            |
      | black_sandal | sandals, summer_collection |
    And I am logged in as "Mary"
    And I am on the products grid
    And I select row blue_sandal
    And I select all visible entities
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I am on the products grid
    Then I should not see products blue_sandal and black_sandal

  Scenario: Successfully mass delete a selection of products categorized in at least one category I own
    Given the following products:
      | sku        | categories                      |
      | blue_boot  | winter_boots, winter_collection |
      | black_boot | winter_boots, winter_collection |
    And I am logged in as "Mary"
    And I am on the products grid
    And I select row blue_boot
    And I select all visible entities
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I am on the products grid
    Then I should not see products blue_boot and black_boot

  Scenario: Successfully mass delete a selection of products with at least one product I don't own
    Given the following category:
      | code    | label-en_US | parent          |
      | shoes   | Shoes       | 2014_collection |
    And the following product category accesses:
      | product category | user group | access |
      | shoes            | Manager    | own    |
      | shoes            | Redactor   | edit   |
    Given the following products:
      | sku          | categories        |
      | blue_sandal  | winter_collection |
      | black_sandal | shoes             |
    And I am logged in as "Mary"
    And I am on the products grid
    And I select row blue_sandal
    And I select all visible entities
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I am on the products grid
    Then I should see product black_sandal
    But I should not see product blue_sandal

  Scenario: Failed to mass delete a selection of products with at least one published product
    Given the following products:
      | sku        | categories                      |
      | blue_boot  | winter_boots, winter_collection |
      | black_boot | winter_boots, winter_collection |
    And I am logged in as "Julia"
    And I publish the product "blue_boot"
    And I am on the products grid
    And I select row blue_boot
    And I select all visible entities
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I am on the products grid
    Then I should see products blue_boot and black_boot
