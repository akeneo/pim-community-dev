@javascript
Feature: Filter product and product models
  In order to filter and show product and product models in the same grid
  As a regular user
  I need to be able to show and filter products and product models in the catalog

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Mary"

  @critical
  Scenario: Successfully filter and display both products and product models
    Given I am on the products grid
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
