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
      | sku    |
      | postit |
      | mug    |
    And the following product values:
      | product | attribute   | value             |
      | postit  | sole_color  | Red               |
      | postit  | sole_fabric | Cashmerewool,Silk |
    And I am logged in as "Mary"
    And I am on the products page

  Scenario: Successfully filter products by reference data
    Given I should not see the filter sole_color
    And the grid should contain 2 elements
    And I should be able to use the following filters:
      | filter      | value                 | result |
      | Sole color  | red                   | postit |
      | Sole color  | red,blue              | postit |
      | Sole color  | is empty              | mug    |
      | Sole color  | Green                 |        |
      | Sole fabric | Cashmerewool          | postit |
      | Sole fabric | Cashmerewool,Neoprene | postit |
      | Sole fabric | Silk                  | postit |
      | Sole fabric | Neoprene              |        |
      | Sole fabric | is empty              | mug    |

  Scenario: Successfully filter product with multi reference data filters
    Given I show the filter "Sole color"
    And I filter by "Sole color" with value "Red"
    And I should be able to use the following filters:
      | filter      | value                 | result |
      | Sole fabric | Cashmerewool          | postit |
      | Sole fabric | Silk                  | postit |
      | Sole fabric | Cashmerewool,Silk     | postit |
      | Sole fabric | Cashmerewool,Neoprene | postit |
      | Sole fabric | Neoprene              |        |
      | Sole fabric | is empty              |        |
