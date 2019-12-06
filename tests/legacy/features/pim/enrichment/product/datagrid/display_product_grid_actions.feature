@javascript
Feature: Display product datagrid row actions
  In order to apply product or product model actions
  As a product manager
  I need to be able to view grid row actions for products and product models

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And the following root product models:
      | code      | family_variant      | categories |
      | tshirt    | clothing_color_size | tshirts    |

  Scenario: Display row actions for product models
    When I am on the products grid

  Scenario: Display row actions for products and product models
    When I am on the products grid
    Then I should be able to view the "Edit attributes of the product" action of the row which contains "watch"
    And I should be able to view the "Classify the product" action of the row which contains "watch"
    And I should be able to view the "Delete the product" action of the row which contains "watch"
    And I should be able to view the "Toggle status" action of the row which contains "watch"
    Then I should be able to view the "Edit attributes of the product" action of the row which contains "tshirt"
    And I should be able to view the "Classify the product" action of the row which contains "tshirt"
    And I should not be able to view the "Delete the product" action of the row which contains "tshirt"
    And I should not be able to view the "Toggle status" action of the row which contains "tshirt"
