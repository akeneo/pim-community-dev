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
    And the following reference data:
      | type   | code         | label        |
      | color  | red          | Red          |
      | color  | blue         |              |
      | color  | green        | Green        |
      | fabric | cashmerewool | Cashmerewool |
      | fabric | neoprene     |              |
      | fabric | silk         | Silk         |

  Scenario: Successfully add reference data values to a product
    Given I am logged in as "Mary"
    And I am on the "high-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Heel color  | Red              |
      | Sole fabric | [neoprene], Silk |
    When I save the product
    Then I should be on the product "high-heels" edit page
    Then the product Heel color should be "red"
    Then the product Sole fabric should be "neoprene, silk"

  Scenario: Successfully edit reference data values to a product
    Given I am logged in as "Mary"
    And the following product values:
      | product    | attribute   | value          |
      | high-heels | heel_color  | red            |
      | high-heels | sole_fabric | neoprene, silk |
    And I am on the "high-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Heel color  | [blue]               |
      | Sole fabric | Cashmerewool, Silk |
    When I save the product
    Then I should be on the product "high-heels" edit page
    Then the product Heel color should be "blue"
    Then the product Sole fabric should be "cashmerewool, silk"

  Scenario: Successfully remove reference data values to a product
    Given I am logged in as "Mary"
    And the following product values:
      | product    | attribute   | value          |
      | high-heels | heel_color  | Red            |
      | high-heels | sole_fabric | Neoprene, Silk |
    And I am on the "high-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Sole fabric |  |
    When I save the product
    Then I should be on the product "high-heels" edit page
    Then the product Sole fabric should be ""
