@javascript
Feature: Filter available products for a variant group
  In order to easily browse products inside a variant group
  As a user
  I need to be able to filter products in a variant group

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family   |
      | black_boots | boots    |
      | white_boots | boots    |
      | blue_boots  | sneakers |
      | sneakers    | sneakers |
    And the following product values:
      | product      | attribute   | value             | locale | scope  |
      | black_boots  | color       | black             |        |        |
      | white_boots  | color       | white             |        |        |
      | blue_boots   | color       | blue              |        |        |
      | sneakers     | color       | black             |        |        |
      | black_boots  | size        | 41                |        |        |
      | white_boots  | size        | 42                |        |        |
      | blue_boots   | size        | 43                |        |        |
      | black_boots  | name        | Black boots       | en_US  |        |
      | white_boots  | name        | White boots       | en_US  |        |
      | blue_boots   | name        | Blue boots        | en_US  |        |
      | black_boots  | description | Nice boots        | en_US  | mobile |
      | black_boots  | description | Nice shiny boots  | en_US  | tablet |
      | white_boots  | description | Great boots       | en_US  | mobile |
      | white_boots  | description | Great shiny boots | en_US  | tablet |
      | blue_boots   | description | Nice boots        | en_US  | mobile |
      | blue_boots   | description | Nice shiny boots  | en_US  | tablet |
      | black_boots  | price       | 45 EUR, 60 USD    |        |        |
      | white_boots  | price       | 50 EUR, 65 USD    |        |        |
      | blue_boots   | price       | 45 EUR, 60 USD    |        |        |
      | sneakers     | price       | 40 EUR, 55 USD    |        |        |
    And I am logged in as "admin"
    And I am on the "caterpillar_boots" variant group page

  Scenario: Successfully display filters on the product datagrid when I edit a variant group
    Then I should see the filters Has product, SKU, Color, Size and Family
    And I should not see the filters Created at and Updated at
    And the grid should contain 3 elements
    And I should see products black_boots, white_boots and blue_boots
    And I should not see product sneakers

  Scenario: Successfully filter by SKU
    Given I filter by "SKU" with value "bl"
    Then the grid should contain 2 elements
    And I should see products black_boots and blue_boots
    And I should not see products white_boots and sneakers

  Scenario: Successfully filter by Family
    Given I filter by "Family" with value "Sneakers"
    Then the grid should contain 1 element
    And I should see product blue_boots
    And I should not see products black_boots, white_boots and sneakers

  Scenario: Successfully filter by Color
    Given I make visible the filter "Color"
    And I filter by "Color" with value "Black"
    Then the grid should contain 1 element
    And I should see product black_boots
    And I should not see products white_boots, sneakers and blue_boots

  Scenario: Successfully filter by Size
    Given I make visible the filter "Size"
    And I filter by "Size" with value "42"
    Then the grid should contain 1 element
    And I should see product white_boots
    And I should not see products black_boots, sneakers and blue_boots

  Scenario: Successfully filter by localizable field
    Given I make visible the filter "Name"
    And I filter by "Name" with value "bl"
    Then the grid should contain 2 elements
    And I should see products black_boots and blue_boots
    And I should not see products white_boots and sneakers

  Scenario: Successfully filter by localizable and scopable field
    Given I make visible the filter "Description"
    And I filter by "Description" with value "great"
    Then the grid should contain 1 elements
    And I should see product white_boots
    And I should not see products black_boots, blue_boots and sneakers

  Scenario: Successfully filter by price
    Given I make visible the filter "Price"
    And I filter per price < "47" and currency "EUR"
    Then the grid should contain 2 elements
    And I should see products black_boots and blue_boots
    And I should not see products sneakers and white_boots

  Scenario: Successfully filter by has product
    Given I filter by "Has product" with value "no"
    Then the grid should contain 3 elements
    And I should see products black_boots, white_boots and blue_boots
