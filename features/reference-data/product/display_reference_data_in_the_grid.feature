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
    And the following reference data:
      | type   | code         | label        |
      | color  | red          | Red          |
      | color  | blue         |              |
      | color  | green        | Green        |
      | fabric | cashmerewool | Cashmerewool |
      | fabric | neoprene     |              |
      | fabric | silk         | Silk         |
    Given the following product values:
      | product    | attribute   | value                  |
      | high-heels | sole_fabric | cashmerewool, neoprene |
      | high-heels | sole_color  | red                    |
    And I am on the products grid
    When I display the columns SKU, Sole color and Sole fabric
    Then the row "high-heels" should contain:
      | column      | value                    |
      | Sole color  | Red                      |
      | Sole fabric | Cashmerewool, [neoprene] |

  Scenario: Successfully edit reference data values to a product with scope
    Given the following products:
      | sku        | family |
      | high-heels | heels  |
    And the following reference data:
      | type   | code   | label  |
      | color  | black  | Black  |
      | color  | purple | Purple |
      | color  | orange |        |
      | fabric | cotton |        |
      | fabric | flax   | Flax   |
      | fabric | straw  | Straw  |
    Given the following product values:
      | product    | attribute   | value         | scope  | locale |
      | high-heels | lace_fabric | Cotton, Straw | tablet | en_US  |
      | high-heels | lace_fabric | Flax, Cotton  | mobile | en_US  |
      | high-heels | cap_color   | Purple        | tablet | en_US  |
      | high-heels | cap_color   | Orange        | mobile | en_US  |
    And I am on the products grid
    When I display the columns SKU, Cap color and Lace fabric
    Then the row "high-heels" should contain:
      | column      | value           |
      | Cap color   | Purple          |
      | Lace fabric | [cotton], Straw |
    When I switch the scope to "Mobile"
    Then the row "high-heels" should contain:
      | column      | value          |
      | Cap color   | [orange]       |
      | Lace fabric | [cotton], Flax |
