@javascript @jira https://akeneo.atlassian.net/browse/PIM-1920
Feature: Update product history when mass editing products
  In order see what changes have been made to products in mass edit
  As a product manager
  I need to be able to see the changes made in mass edit in product history

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page
    And I save the product
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | sneakers |
      | family | Sneakers |
    And I press the "Save" button in the popin
    And I wait to be on the "sneakers" product page
    And I save the product
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | sandals |
      | family | Sandals |
    And I press the "Save" button in the popin
    And I wait to be on the "sandals" product page
    And I save the product
    And I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button

  Scenario: Display history when changing product status
    Given I choose the "Change status" operation
    And I disable the products
    And I wait for the "update_product_value" job to finish
    When I edit the "boots" product
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value |
      | 2       | enabled  | 0     |
    When I edit the "sandals" product
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value |
      | 2       | enabled  | 0     |
    When I edit the "sneakers" product
    And I visit the "History" column tab
    Then there should be 2 updates
    And I should see history:
      | version | property | value |
      | 2       | enabled  | 0     |
