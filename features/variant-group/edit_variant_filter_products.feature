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
    And the following products:
      | sku    | family    |
      | MUG_1  | mug       |
      | MUG_2  | mug       |
      | MUG_3  | mug       |
      | POSTIT | furniture |
    And the following product attributes:
      | product | code  | label | type         | required | translatable | scopable |
      | MUG_1   | color | Color | simpleselect | no       | no           | no       |
      | MUG_1   | size  | Size  | simpleselect | no       | no           | no       |
      | MUG_2   | color | Color | simpleselect | no       | no           | no       |
      | MUG_3   | size  | Size  | simpleselect | no       | no           | no       |
      | POSTIT  | color | Color | simpleselect | no       | no           | no       |
      | POSTIT  | size  | Size  | simpleselect | no       | no           | no       |
      | MUG_2   | name  | Name  | text         | no       | yes          | yes      |
    And the following "color" attribute options: Yellow, Blue, Green and Red
    And the following "size" attribute options: XS, S, M, L and XL
    And the following product values:
      | product | attribute | value   |
      | MUG_1   | color     | Red     |
      | MUG_1   | size      | XL      |
      | MUG_2   | color     | Green   |
      | MUG_2   | name      | Mug Two |
      | MUG_3   | size      | S       |
      | POSTIT  | color     | Blue    |
      | POSTIT  | size      | M       |
    And the following variants:
      | code   | label      | attributes  |
      | MUG    | MUG Akeneo | color       |
      | POSTIT | Postit     | color, size |
    And I am logged in as "admin"

  @insulated
  Scenario: Successfully display filters on the product datagrid when I edit a variant group
    Given I am on the "MUG" variant page
    Then I should see the filters SKU and Family
    And I should not see the filters Color, Size, Name, Created at and Updated at
    And the grid should contain 3 elements
    And I should see products MUG_1, MUG_2 and POSTIT
    And I should not see product MUG_3

  @insulated
  Scenario: Successfully display filters on the product datagrid when I edit a variant group with 2 axes
    Given I am on the "POSTIT" variant page
    Then I should see the filters SKU and Family
    And I should not see the filters Color, Size, Created at and Updated at
    And the grid should contain 2 elements
    And I should see products MUG_1 and POSTIT
    And I should not see products MUG_2 and MUG_3

  @insulated
  Scenario: Successfully filter by SKU
    Given I am on the "MUG" variant page
    When I filter by "SKU" with value "MUG"
    Then the grid should contain 2 elements
    And I should see products MUG_1 and MUG_2
    And I should not see products POSTIT and MUG_3

  @insulated
  Scenario: Successfully filter by Family
    Given I am on the "POSTIT" variant page
    When I filter by "Family" with value "Furniture"
    Then the grid should contain 1 element
    And I should see product POSTIT
    And I should not see products MUG_1, MUG_2 and MUG_3

  @insulated
  Scenario: Successfully filter by Color
    Given I am on the "MUG" variant page
    When I make visible the filter "Color"
    And I filter by "Color" with value "Red"
    Then the grid should contain 1 element
    And I should see product MUG_1
    And I should not see products MUG_2, MUG_3 and POSTIT

  @insulated
  Scenario: Successfully filter by Size
    Given I am on the "POSTIT" variant page
    When I make visible the filter "Size"
    And I filter by "Size" with value "XL"
    Then the grid should contain 1 element
    And I should see product MUG_1
    And I should not see products MUG_2, MUG_3 and POSTIT
