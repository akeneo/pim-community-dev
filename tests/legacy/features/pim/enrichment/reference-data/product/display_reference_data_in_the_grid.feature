@javascript
Feature: Display reference data in the grid
  In order to easily see product data in the grid
  As a regular user
  I need to be able to display values for different reference data in the grid

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Mary"

  @critical
  Scenario: Successfully display reference data values of a product
    Given the following products:
      | sku        | family |
      | high-heels | heels  |
    Given the following product values:
      | product    | attribute   | value                  |
      | high-heels | sole_fabric | cashmerewool, neoprene |
      | high-heels | sole_color  | red                    |
    And I am on the products grid
    And I collapse the column
    When I display the columns SKU, Sole color and Sole fabric
    Then the row "high-heels" should contain:
      | column      | value                    |
      | Sole color  | Red                      |
      | Sole fabric | Cashmerewool, Neoprene   |
