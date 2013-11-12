@javascript
Feature: Associate a product
  In order to create associations between products and groups
  As Julia
  I need to associate a product to other products and groups

  Background:
  Given the following products:
    | sku            |
    | black_tea      |
    | african_coffee |
    | indian_coffee  |
    | coffee_mug     |
    | tea_mug        |
    | akeneo_mug     |
  And the following associations:
    | code   | label        |
    | x_sell | Cross sell   |
    | upsell | Upsell       |
    | subst  | Substitution |
  And the following product groups:
    | code             | label                  | attributes | type   |
    | x_sell_beverages | Cross sell beverages   |            | X_SELL |
    | upsell_beverages | Upsell beverages       |            | X_SELL |
    | subst_beverages  | Substitution beverages |            | X_SELL |
  And I am logged in as "Julia"

  Scenario: Associate a product to another product
    Given I edit the "african_coffee" product
    When I visit the "Associations" tab
    And I visit the "Cross sell" group
    And I check the row "coffee_mug"
    And I press the "Save" button
    Then the row "coffee_mug" should be checked

  Scenario: Associate a product to another group
    Given I edit the "black_tea" product
    When I visit the "Associations" tab
    And I visit the "Upsell" group
    And I press the "Show groups" button
    And I check the row "upsell_beverages"
    And I press the "Save" button
    And I press the "Show groups" button
    Then the row "upsell_beverages" should be checked

  Scenario: Associate a product to multiple products and groups
    Given I edit the "black_tea" product
    When I visit the "Associations" tab
    And I visit the "Substitution" group
    And I check the row "indian_coffee"
    And I visit the "Upsell" group
    And I check the rows "coffee_mug and tea_mug"
    And I press the "Show groups" button
    And I check the row "upsell_beverages"
    And I visit the "Cross sell" group
    And I check the rows "x_sell_beverages and subst_beverages"
    And I press the "Show products" button
    And I check the rows "akeneo_mug, african_coffee and tea_mug"
    And I press the "Save" button
    And I visit the "Cross sell" group
    Then I should see "3 products and 2 groups"
    And I visit the "Upsell" group
    Then I should see "2 products and 1 groups"
    And I visit the "Substitution" group
    Then I should see "1 products and 0 groups"
