@javascript
Feature: Apply a mass action on products and product models
  In order to modify my catalog
  As a product manager
  I need to be able to select products and product models at the same time in the grid and apply mass-edit on them

  Background:
    Given a "catalog_modeling" catalog configuration
    And there is no "product" in the catalog
    And there is no "product model" in the catalog
    And the following root product models:
      | code                | parent | family_variant      | categories | price          | color | description-en_US-ecommerce |
      | tshirt-unique-color |        | clothing_color_size | master_men | 10 USD, 15 EUR | blue  | A unique color t-shirt      |
    And the following products:
      | sku                  | family   | name-en_US           | categories | size | description-en_US-ecommerce |
      | tshirt-kurt-cobain-s | clothing | Tshirt Kurt Cobain S | Tshirts    | S    | A Kurt Cobain t-shirt       |
    And I am logged in as "Julia"

  Scenario: Apply a mass action on products and product models
    Given I am on the products page
    And I select rows tshirt-unique-color and tshirt-kurt-cobain-s
    And I press "Bulk actions" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Model description attribute
    And I change the "Model description" to "a tee"
    When I move to the confirm page
    Then I should see the text "You are about to update 2 products with the following information, please confirm."

