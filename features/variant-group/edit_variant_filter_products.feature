@javascript
Feature: Edit a variant group adding/removing products
  In order to manage existing variant groups for the catalog
  As a user
  I need to be able to add and remove product from a variant group

  Background:
    Given the following families:
      | code      | label     |
      | mug       | Mug       |
      | furniture | Furniture |
    And the following currencies:
      | code | activated |
      | USD  | yes       |
      | EUR  | yes       |
      | GBP  | no        |
    And the following products:
      | sku    | family    |
      | MUG_1  | mug       |
      | MUG_2  | mug       |
      | MUG_3  | mug       |
      | POSTIT | furniture |
    And the following product attributes:
      | product | code  | label       | type         | required | translatable | scopable |
      | MUG_2   | name  | Name        | text         | no       | no           | yes      |
      | MUG_2   | title | Title       | text         | no       | yes          | no       |
      | MUG_2   | descr | Description | text         | no       | yes          | yes      |
      | MUG_2   | price | Price       | prices       | no       | no           | no       |
      | MUG_3   | size  | Size        | simpleselect | no       | no           | no       |
      | MUG_3   | descr | Description | text         | no       | yes          | yes      |
      | POSTIT  | color | Color       | simpleselect | no       | no           | no       |
      | POSTIT  | size  | Size        | simpleselect | no       | no           | no       |
      | POSTIT  | title | Title       | text         | no       | yes          | no       |
      | POSTIT  | descr | Description | text         | no       | yes          | yes      |
      | POSTIT  | price | Price       | prices       | no       | no           | no       |
      | MUG_1   | color | Color       | simpleselect | no       | no           | no       |
      | MUG_1   | size  | Size        | simpleselect | no       | no           | no       |
      | MUG_1   | price | Price       | prices       | no       | no           | no       |
      | MUG_2   | color | Color       | simpleselect | no       | no           | no       |
    And the following "color" attribute options: Yellow, Blue, Green and Red
    And the following "size" attribute options: XS, S, M, L and XL
    And the following product values:
      | product | attribute   | value              | locale | scope     |
      | MUG_1   | color       | Red                |        |           |
      | MUG_1   | size        | XL                 |        |           |
      | MUG_1   | price       | 30.5               |        |           |
      | MUG_2   | color       | Green              |        |           |
      | MUG_2   | name        | Mug Ecommerce      |        | ecommerce |
      | MUG_2   | name        | Mug Mobile         |        | mobile    |
      | MUG_2   | title       | My mug             | en_US  |           |
      | MUG_2   | title       | Mon mug            | fr_FR  |           |
      | MUG_2   | description | My ecommerce descr | en_US  | ecommerce |
      | MUG_2   | description | Ma descr ecommerce | fr_FR  | ecommerce |
      | MUG_2   | price       | 35                 |        |           |
      | MUG_3   | size        | S                  |        |           |
      | MUG_3   | description | My ecommerce descr | en_US  | ecommerce |
      | POSTIT  | color       | Blue               |        |           |
      | POSTIT  | size        | M                  |        |           |
      | POSTIT  | title       | Post-it title      | en_US  |           |
      | POSTIT  | title       | Titre étiquette    | fr_FR  |           |
      | POSTIT  | description | English ecommerce  | en_US  | ecommerce |
      | POSTIT  | description | French ecommerce   | fr_FR  | ecommerce |
      | POSTIT  | description | English mobile     | en_US  | mobile    |
      | POSTIT  | description | French mobile      | fr_FR  | mobile    |
      | POSTIT  | price       | 40                 |        |           |
    And the following product groups:
      | code   | label      | attributes  | type    |
      | MUG    | MUG Akeneo | color       | VARIANT |
      | POSTIT | Postit     | color, size | VARIANT |
    And I am logged in as "admin"

  Scenario: Successfully display filters on the product datagrid when I edit a variant group
    Given I am on the "MUG" variant group page
    Then I should see the filters Has product, SKU, Color and Family
    And I should not see the filters Created at and Updated at
    And the grid should contain 3 elements
    And I should see products MUG_1, MUG_2 and POSTIT
    And I should not see product MUG_3

  Scenario: Successfully display filters on the product datagrid when I edit a variant group with 2 axes
    Given I am on the "POSTIT" variant group page
    Then I should see the filters Has product, SKU, Color, Size and Family
    And I should not see the filters Created at and Updated at
    And the grid should contain 2 elements
    And I should see products MUG_1 and POSTIT
    And I should not see products MUG_2 and MUG_3

  Scenario: Successfully filter by SKU
    Given I am on the "MUG" variant group page
    When I filter by "SKU" with value "MUG"
    Then the grid should contain 2 elements
    And I should see products MUG_1 and MUG_2
    And I should not see products POSTIT and MUG_3

  Scenario: Successfully filter by Family
    Given I am on the "POSTIT" variant group page
    When I filter by "Family" with value "Furniture"
    Then the grid should contain 1 element
    And I should see product POSTIT
    And I should not see products MUG_1, MUG_2 and MUG_3

  Scenario: Successfully filter by Color
    Given I am on the "MUG" variant group page
    When I make visible the filter "Color"
    And I filter by "Color" with value "Red"
    Then the grid should contain 1 element
    And I should see product MUG_1
    And I should not see products MUG_2, MUG_3 and POSTIT

  Scenario: Successfully filter by Size
    Given I am on the "POSTIT" variant group page
    When I make visible the filter "Size"
    And I filter by "Size" with value "XL"
    Then the grid should contain 1 element
    And I should see product MUG_1
    And I should not see products MUG_2, MUG_3 and POSTIT

  Scenario: Successfully filter by scopable field
    Given I am on the "MUG" variant group page
    When I make visible the filter "Name"
    And I filter by "Name" with value "Ecommerce"
    Then the grid should contain 1 element
    And I should see product MUG_2
    And I should not see products MUG_1, MUG_3 and POSTIT

  Scenario: Successfully filter by localizable field
    Given I am on the "POSTIT" variant group page
    When I make visible the filter "Title"
    And I filter by "Title" with value "title"
    Then the grid should contain 1 element
    And I should see product POSTIT
    And I should not see products MUG_1, MUG_2 and MUG_3

  Scenario: Successfully filter by localizable and scopable field
    Given I am on the "POSTIT" variant group page
    When I make visible the filter "Description"
    And I filter by "Description" with value "English"
    Then the grid should contain 1 element
    And I should see product POSTIT
    And I should not see products MUG_1, MUG_2 and MUG_3

  Scenario: Successfully filter by price
    Given I am on the "MUG" variant group page
    When I make visible the filter "Price"
    And I filter per price < "40" and currency "EUR"
    Then the grid should contain 2 elements
    And I should see product MUG_1 and MUG_2
    And I should not see products MUG_3 and POSTIT

  Scenario: Successfully filter by has product
    Given I am on the "MUG" variant group page
    When I make visible the filter "Has product"
    And I filter by "Has product" with value "no"
    Then the grid should contain 3 elements
    And I should see product MUG_1, MUG_2 and POSTIT
    And I should not see products MUG_3
