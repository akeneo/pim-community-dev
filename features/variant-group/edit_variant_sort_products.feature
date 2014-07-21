@javascript
Feature: Sort available products for a variant group
  In order to easily browse products inside a variant group
  As a product manager
  I need to be able to sort products in a variant group

  Background:
    Given the "default" catalog configuration
    And the following families:
      | code      | label-en_US |
      | mug       | Mug         |
      | furniture | Furniture   |
    And the following attributes:
      | code  | label | type         | useable as grid column |
      | color | Color | simpleselect | yes                    |
      | size  | Size  | simpleselect | yes                    |
    And the following "color" attribute options: Yellow, Blue, Green and Red
    And the following "size" attribute options: XS, S, M, L and XL
    And the following products:
      | sku    | family    | color | size |
      | MUG_1  | mug       | Red   | M    |
      | POSTIT | furniture | Blue  | XL   |
    And the following product groups:
      | code   | label  | attributes  | products | type    |
      | POSTIT | Postit | color, size | POSTIT   | VARIANT |
    And I am logged in as "Julia"

  Scenario: Successfully sort products
    Given I am on the "POSTIT" variant group page
    Then the rows should be sorted descending by In group
    And I should be able to sort the rows by In group, SKU, Color, Size, Family, Created at and Updated at
