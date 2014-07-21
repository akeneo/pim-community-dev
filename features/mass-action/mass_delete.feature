@javascript
Feature: Delete many product at once
  In order to easily manage products
  As a product manager
  I need to be able to remove many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku          | family   | categories        | name-en_US    | price          | size |
      | boots_S36    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 36   |
      | boots_S37    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 37   |
      | boots_S38    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 38   |
      | boots_S39    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 39   |
      | boots_S40    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 40   |
      | boots_S41    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 41   |
      | sneakers_S39 | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 39   |
      | sneakers_S40 | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 40   |
      | sneakers_S41 | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 41   |
      | sneakers_S42 | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 42   |
      | sneakers_S43 | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 43   |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Display a message when try to delete none product
    Given I press mass-delete button
    Then I should see flash message "No products selected"

  Scenario: Successfully remove many products
    Given I mass-delete products boots_S36, boots_S37 and boots_S38
    Then I should see "Are you sure you want to delete selected products?"
    When I confirm the removal
    Then I should not see products boots_S36, product boots_S37 and boots_S38
    And the grid should contain 8 elements

  Scenario: Successfully "mass" delete one product
    Given I mass-delete product boots_S38
    Then I should see "Are you sure you want to delete selected products?"
    When I confirm the removal
    Then I should not see product boots_S38
    And the grid should contain 10 elements

  Scenario: Successfully mass delete visible products
    Given I sort by "SKU" value ascending
    And I select all visible products
    Then I press mass-delete button
    And I should see "Are you sure you want to delete selected products?"
    When I confirm the removal
    Then the grid should contain 1 element
    And I should see product sneakers_S43

  Scenario: Successfully mass delete all products
    Given I select all products
    Then I press mass-delete button
    And I should see "Are you sure you want to delete selected products?"
    When I confirm the removal
    Then the grid should contain 0 elements
