@javascript
Feature: Edit a product
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku        | family |
      | high-heels | heels  |

  @critical
  Scenario: Successfully add reference data values to a product
    Given I am logged in as "Mary"
    And I am on the "high-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Heel color  | Tufts Blue             |
      | Sole fabric | Cashmerewool, SilkNoil |
    When I save the product
    Then I should be on the product "high-heels" edit page
    Then the product Heel color should be "tufts-blue"
    Then the product Sole fabric should be "cashmerewool, silknoil"

  @critical
  Scenario: Successfully edit reference data values to a product
    Given I am logged in as "Mary"
    And the following product values:
      | product    | attribute   | value          |
      | high-heels | heel_color  | red            |
      | high-heels | sole_fabric | neoprene, silk |
    And I am on the "high-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Heel color  | Tufts Blue              |
      | Sole fabric | Cashmerewool, SilkNoil |
    When I save the product
    Then I should be on the product "high-heels" edit page
    Then the product Heel color should be "tufts-blue"
    Then the product Sole fabric should be "cashmerewool, silknoil"
