@javascript
Feature: Display reference data in the grid
  In order to easily see product data in the grid
  As a regular user
  I need to be able to display values for different reference data in the grid

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku        | family |
      | high-heels | heels  |
    And the following "sole_color" attribute reference data: Red, Blue and Green
    And the following "sole_fabric" attribute reference data: Cashmerewool, Neoprene and Silk
    And I am logged in as "Mary"

  Scenario: Successfully edit reference data values to a product
    Given the following product values:
      | product    | attribute   | value                  |
      | high-heels | sole_fabric | Cashmerewool, Neoprene |
      | high-heels | sole_color  | Red                    |
    And I am on the products page
    When I display the columns sku, sole_color and sole_fabric
    Then the row "high-heels" should contain:
      | column      | value                  |
      | Sole color  | Red                    |
      | Sole fabric | Cashmerewool, Neoprene |
