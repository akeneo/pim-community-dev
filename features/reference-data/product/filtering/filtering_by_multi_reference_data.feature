@javascript
Feature: Filter products by reference data
  In order to filter products in the catalog per reference data
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "footwear" catalog configuration
    And the following "sole_color" attribute reference data: Red, Blue and Green
    And the following "sole_fabric" attribute reference data: Cashmerewool, Neoprene and Silk
    And the following products:
      | sku    | family |
      | postit | heels  |
      | mug    | heels  |
      | book   | heels  |
    And the following product values:
      | product | attribute   | value             |
      | postit  | sole_color  | Red               |
      | postit  | sole_fabric | Cashmerewool,Silk |
    And the "mug" product has the "sole_fabric" attributes
    And I am logged in as "Mary"
    And I am on the products grid

  @skip @info To be unskipped in PIM-6574
  Scenario: Successfully filter product with multi reference data filters
    And I should be able to use the following filters:
      | filter      | operator     | value                 | result |
      | sole_fabric | in list      | Cashmerewool          | postit |
      | sole_fabric | in list      | Silk                  | postit |
      | sole_fabric | in list      | Cashmerewool,Silk     | postit |
      | sole_fabric | in list      | Cashmerewool,Neoprene | postit |
      | sole_fabric | in list      | Neoprene              |        |
      | sole_fabric | is empty     |                       | mug    |
      | sole_fabric | is not empty |                       | postit |
