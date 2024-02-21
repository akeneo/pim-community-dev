@javascript
Feature: Sort products
  In order to enrich my catalog
  As a regular user
  I need to be able to manually sort products per reference data attributes

  Scenario: Successfully sort products by simple reference data
    Given the "footwear" catalog configuration
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
    When I am on the products grid
    Then the grid should contain 2 elements
    When I display the columns SKU, Sole color, Heel color and Sole fabric
    Then I sort by "Sole color" value ascending
