@javascript
Feature: Display reference data in the grid
  In order to easily see product data in the grid
  As a regular user
  I need to be able to display values for different reference data in the grid

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully edit reference data values to a product
    Given the following products:
      | sku        | family |
      | high-heels | heels  |
    And the following "sole_color" attribute reference data: Red, Blue and Green
    And the following "sole_fabric" attribute reference data: Cashmerewool, Neoprene and Silk
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

  Scenario: Successfully edit reference data values to a product with scope
    Given the following products:
      | sku        | family |
      | high-heels | heels  |
    And the following "cap_color" attribute reference data: Black, Purple and Orange
    And the following "lace_fabric" attribute reference data: Cotton, Flax and Straw
    Given the following product values:
      | product    | attribute   | value         | scope  | locale |
      | high-heels | lace_fabric | Cotton, Straw | tablet | en_US  |
      | high-heels | lace_fabric | Flax, Cotton  | mobile | en_US  |
      | high-heels | cap_color   | Purple        | tablet | en_US  |
      | high-heels | cap_color   | Orange        | mobile | en_US  |
    And I am on the products page
    When I display the columns sku, cap_color and lace_fabric
    Then the row "high-heels" should contain:
      | column      | value         |
      | Cap color   | Purple        |
      | Lace fabric | Cotton, Straw |
    When I filter by "Channel" with value "Mobile"
      Then the row "high-heels" should contain:
      | column      | value         |
      | Cap color   | Orange        |
      | Lace fabric | Cotton, Flax  |
