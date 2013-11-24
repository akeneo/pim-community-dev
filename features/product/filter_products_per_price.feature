@javascript
Feature: Filter products per price
  In order to filter products in the catalog per price
  As a user
  I need to be able to filter products per price in the catalog

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following products:
      | sku    | family    | enabled |
      | postit | furniture | yes     |
      | book   | library   | no      |
    And a "postit" product
    And a "book" product
    And the following attributes:
      | label | required | translatable | scopable | type   |
      | SKU   | yes      | no           | no       | text   |
      | price | no       | no           | yes      | prices |
    And the following product values:
      | product | attribute | scope     | value  |
      | postit  | SKU       |           | postit |
      | postit  | price     | mobile    | 10.5   |
      | postit  | price     | ecommerce | 12.5   |
      | book    | SKU       |           | book   |
      | book    | price     | mobile    | 20     |
      | book    | price     | ecommerce | 22.5   |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the products page
    Then I should see the filter SKU
    And I should not see the filter Price
    And the grid should contain 2 elements
    And I should see products postit and book

  Scenario: Successfully filter per Price with "greater or equal" action
    Given I am on the products page
    When I make visible the filter "Price"
    And I filter per price >= "20" and currency "EUR"
    Then the grid should contain 1 element
    And I should see product book
    And I should not see product postit

  Scenario: Successfully filter per Price with "greater than" action and decimal number
    Given I am on the products page
    When I make visible the filter "Price"
    And I filter per price > "12.5" and currency "EUR"
    Then the grid should contain 1 element
    And I should see product book
    And I should not see product postit

  Scenario: Successfully filter per Price with "equal" action
    Given I am on the products page
    When I make visible the filter "Price"
    And I filter per price = "12.5" and currency "EUR"
    Then the grid should contain 1 element
    And I should see product postit
    And I should not see product book

  Scenario: Successfully filter per Price with "less than" action
    Given I am on the products page
    When I make visible the filter "Price"
    And I filter per price < "20" and currency "EUR"
    Then the grid should contain 1 element
    And I should see product postit
    And I should not see product book

  Scenario: Successfully filter per Price with "less or equal" action
    Given I am on the products page
    When I make visible the filter "Price"
    And I filter per price <= "13" and currency "EUR"
    Then the grid should contain 1 element
    And I should see product postit
    And I should not see product book

  Scenario: Successfully filter per Price with useless filter
    Given I am on the products page
    When I make visible the filter "Price"
    And I filter per price <= "20" and currency "EUR"
    Then the grid should contain 2 elements
    And I should see product postit and book

  Scenario: Successfully filter per Price with no result values
    Given I am on the products page
    When I make visible the filter "Price"
    And I filter per price > "40.5" and currency "EUR"
    Then the grid should contain 0 element
    And I should not see products book and postit
