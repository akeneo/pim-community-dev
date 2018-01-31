@javascript
Feature: Filter product and product models
  In order to filter and show product and product models in the same grid
  As a regular user
  I need to be able to show and filter products and product models in the catalog

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully filter and display both products and product models
    Given I am on the products page
    When I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    Then I should see products tshirt-unique-size-crimson-red, running-shoes-xxs-crimson-red, running-shoes-m-crimson-red, running-shoes-xxxl-crimson-red
    And I should see the product models model-tshirt-divided-crimson-red, model-tshirt-unique-color-kurt
    And the row "model-tshirt-divided-crimson-red" should contain:
      | column           | value                            |
      | ID               | model-tshirt-divided-crimson-red |
      | label            | Divided crimson red              |
      | family           | Clothing                         |
      | Status           |                                  |
      | complete         | N/A                              |
    And the row "running-shoes-xxs-crimson-red" should contain:
      | column           | value                         |
      | ID               | running-shoes-xxs-crimson-red |
      | label            | Running shoes XXS             |
      | family           | Shoes                         |
      | Status           | Enabled                       |
      | complete         | 62%                           |

  Scenario: View products and product models with the same ID in the grid
    Given there is no "product" in the catalog
    And there is no "product model" in the catalog
    And the following root product models:
      | code                | parent | family_variant      | categories | price          | color | description-en_US-ecommerce |
      | tshirt-unique-color |        | clothing_color_size | master_men | 10 USD, 15 EUR | blue  | A unique color t-shirt      |
    And the following products:
      | sku                  | family   | name-en_US           | categories | size | description-en_US-ecommerce |
      | tshirt-kurt-cobain-s | clothing | Tshirt Kurt Cobain S | Tshirts    | S    | A Kurt Cobain t-shirt       |
    When I am on the products page
    Then I should see products tshirt-unique-color
    And I should see the product models tshirt-kurt-cobain-s

  Scenario: Successfully filters on the parent field with codes
    Given I am on the products grid
    When I filter by "parent" with operator "in list" and value "model-braided-hat,diana"
    Then I should see products braided-hat-m, braided-hat-xxxl, diana_pink, diana_red

  Scenario: Successfully filters on the parent field with empty operator
    Given I am on the products grid
    And I show the filter "weight"
    And I filter by "weight" with operator "is not empty" and value ""
    When I filter by "parent" with operator "is empty" and value ""
    Then I should see products Scarf, Sunglasses, Bag, Belt, Hat
