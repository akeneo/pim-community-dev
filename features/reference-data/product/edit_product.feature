Feature: Edit a product
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku        | family |
      | high-heels | heels  |
    And the following "sole_color" attribute reference data: Red, Blue and Green
    And the following "sole_fabric" attribute reference data: Cashmerewool, Neoprene and Silk

  @javascript
  Scenario: Successfully add reference data values to a product
    Given I am logged in as "Mary"
    And I am on the "high-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Heel color  | Red            |
      | Sole fabric | Neoprene, Silk |
    When I press the "Save" button
    Then I should be on the product "high-heels" edit page
    Then the product Heel color should be "Red"
    Then the product Sole fabric should be "Neoprene, Silk"

  @javascript
  Scenario: Successfully edit reference data values to a product
    Given I am logged in as "Mary"
    And the following product values:
      | product    | attribute   | value          |
      | high-heels | heel_color  | Red            |
      | high-heels | sole_fabric | Neoprene, Silk |
    And I am on the "high-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Heel color  | Blue               |
      | Sole fabric | Cashmerewool, Silk |
    When I press the "Save" button
    Then I should be on the product "high-heels" edit page
    Then the product Heel color should be "Red"
    Then the product Sole fabric should be "Cashmerewool, Silk"

  @javascript
  Scenario: Successfully edit reference data values to a product
    Given I am logged in as "Mary"
    And the following product values:
      | product    | attribute   | value          |
      | high-heels | heel_color  | Red            |
      | high-heels | sole_fabric | Neoprene, Silk |
    And I am on the "high-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Sole fabric | |
    When I press the "Save" button
    Then I should be on the product "high-heels" edit page
    Then the product Sole fabric should be ""
