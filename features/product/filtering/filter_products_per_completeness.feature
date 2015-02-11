@javascript
Feature: Filter products
  In order to filter products in the catalog per completeness
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku     | family | name-en_US    | price             | size | color |
      | BOOTBXS | boots  | Boot 42 Black | 10.00 USD, 10 EUR | 42   | black |
      | BOOTWXS | boots  | Boot 42 Black | 10.00 USD, 10 EUR | 42   |       |
      | BOOTBS  | boots  |               |                   | 38   | black |
      | BOOTBL  | boots  |               |                   |      |       |
      | BOOTRXS | boots  | Boot 42 Red   |                   |      |       |
    And I launched the completeness calculator

  Scenario: Successfully filter uncomplete products for default mobile channel
    Given I am logged in as "Peter"
    And I am on the products page
    And I filter by "Complete" with value "no"
    Then the grid should contain 4 elements
    And I should see products BOOTWXS, BOOTBS, BOOTBL, BOOTRXS

  Scenario: Successfully filter complete products for default mobile channel
    Given I am logged in as "Peter"
    And I am on the products page
    And I filter by "Complete" with value "yes"
    Then the grid should contain 1 elements
    And I should see products BOOTBXS

  Scenario: Successfully filter uncomplete products for default tablet channel
    Given I am logged in as "Mary"
    And I am on the products page
    And I filter by "Complete" with value "no"
    Then the grid should contain 5 elements
    And I should see products BOOTBXS, BOOTWXS, BOOTBS, BOOTBL, BOOTRXS
    And I filter by "Channel" with value "Mobile"
    Then the grid should contain 4 elements
    And I should see products BOOTWXS, BOOTBS, BOOTBL, BOOTRXS

  Scenario: Successfully filter complete products for default tablet channel
    Given I am logged in as "Mary"
    And I am on the products page
    And I filter by "Complete" with value "yes"
    Then the grid should contain 0 elements
    And I filter by "Channel" with value "Mobile"
    Then the grid should contain 1 elements
    And I should see products BOOTBXS