@javascript
Feature: Sort products
  In order to enrich my catalog
  As a regular user
  I need to be able to manually sort products per attributes

  Background:
    Given the "footwear" catalog configuration
    And the following "sole_color" attribute reference data: Red, Blue and Green
    And the following "sole_fabric" attribute reference data: Cashmerewool, Neoprene and Silk
    And the following "heel_color" attribute reference data: Pink, Purple and Black
    And the following products:
      | sku    |
      | postit |
      | mug    |
    And the following product values:
      | product | attribute   | value             |
      | postit  | sole_color  | Red               |
      | postit  | heel_color  | Pink              |
      | postit  | sole_fabric | Cashmerewool,Silk |
    And I am logged in as "Mary"

  Scenario: Successfully sort products by simple reference data
    Given I am on the products grid
    And the grid should contain 2 elements
    And I display the columns SKU, Sole color, Heel color and Sole fabric
    And I sort by "Sole color" value ascending
