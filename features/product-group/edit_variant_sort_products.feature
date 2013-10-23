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
      | POSTIT | furniture |
    And the following product attributes:
      | product | code  | label | type         | required | translatable | scopable |
      | MUG_1   | color | Color | simpleselect | no       | no           | no       |
      | MUG_1   | size  | Size  | simpleselect | no       | no           | no       |
      | POSTIT  | color | Color | simpleselect | no       | no           | no       |
      | POSTIT  | size  | Size  | simpleselect | no       | no           | no       |
    And the following "color" attribute options: Yellow, Blue, Green and Red
    And the following "size" attribute options: XS, S, M, L and XL
    And the following product values:
      | product | attribute | value |
      | MUG_1   | color     | Red   |
      | MUG_1   | size      | M     |
      | POSTIT  | color     | Blue  |
      | POSTIT  | size      | XL    |
    And the following product groups:
      | code   | label      | attributes  | products | type    |
      | POSTIT | Postit     | color, size | POSTIT   | VARIANT |
    And I am logged in as "admin"

  Scenario: Successfully display the sortable columns
    Given I am on the "POSTIT" product group page
    Then the rows should be sortable by Has product, SKU, Color, Size, Family, Created at and Updated at
    And the rows should be sorted ascending by Has product

  Scenario: Successfully sort products by Has product ascending
    Given I am on the "POSTIT" product group page
    When I sort by "Has product" value ascending
    Then I should see sorted products MUG_1 and POSTIT
    
    When I sort by "Has product" value descending
    Then I should see sorted products POSTIT and MUG_1

  @skip
  Scenario: Successfully sort products by SKU ascending
    Given I am on the "POSTIT" product group page
    When I sort by "SKU" value ascending
    Then I should see sorted products MUG_1 and POSTIT

    When I sort by "SKU" value descending
    Then I should see sorted products POSTIT and MUG_1

  Scenario: Successfully sort products by color ascending
    Given I am on the "POSTIT" product group page
    When I sort by "Color" value ascending
    Then I should see sorted products POSTIT and MUG_1

    When I sort by "Color" value descending
    Then I should see sorted products MUG_1 and POSTIT

  Scenario: Successfully sort products by size ascending
    Given I am on the "POSTIT" product group page
    When I sort by "Size" value ascending
    Then I should see sorted products MUG_1 and POSTIT

    When I sort by "Size" value descending
    Then I should see sorted products POSTIT and MUG_1

  Scenario: Successfully sort products by family ascending
    Given I am on the "POSTIT" product group page
    When I sort by "Family" value ascending
    Then I should see sorted products POSTIT and MUG_1

    When I sort by "Family" value descending
    Then I should see sorted products MUG_1 and POSTIT
