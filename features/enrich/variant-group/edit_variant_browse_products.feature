@javascript
Feature: Edit a variant group adding/removing products
  In order to manage existing variant groups for the catalog
  As a product manager
  I need to be able to add and remove product from a variant group

  Background:
    Given the "default" catalog configuration
    And the following families:
      | code      | label-en_US |
      | mug       | Mug         |
      | furniture | Furniture   |
    And the following attributes:
      | code  | label | type         | useable_as_grid_filter |
      | color | Color | simpleselect | yes                    |
      | size  | Size  | simpleselect | yes                    |
    And the following "color" attribute options: Yellow, Blue, Green, Pink and Red
    And the following "size" attribute options: XS, S, M, L and XL
    And the following product groups:
      | code       | label      | axis        | type    |
      | MUG        | MUG Akeneo | color       | VARIANT |
      | POSTIT     | Postit     | color, size | VARIANT |
      | CROSS_SELL | Cross sell |             | X_SELL |
    And the following products:
      | sku    | groups          | family    | color  | size |
      | MUG_A1 |                 | mug       |        |      |
      | MUG_A2 |                 | mug       | Red    | XL   |
      | MUG_A3 |                 | mug       | Green  |      |
      | MUG_A4 |                 | mug       |        | S    |
      | MUG_B1 | MUG             | mug       | Yellow | M    |
      | MUG_B2 | MUG             | mug       | Blue   |      |
      | MUG_C1 | CROSS_SELL      | mug       |        |      |
      | MUG_C2 | CROSS_SELL      | mug       | Yellow | M    |
      | MUG_C3 | CROSS_SELL      | mug       | Red    |      |
      | MUG_C4 | CROSS_SELL      | mug       |        | XL   |
      | MUG_D1 | CROSS_SELL, MUG | mug       | Green  | L    |
      | MUG_D2 | CROSS_SELL, MUG | mug       | Pink   |      |
      | POSTIT |                 | furniture | Blue   | M    |
    And I am logged in as "Julia"

  Scenario: Successfully display the product datagrid when I edit a variant group
    Given I am on the "MUG" variant group page
    Then the grid should contain 9 elements
    And I should see products MUG_A2, MUG_A3, MUG_B1, MUG_B2, MUG_C2, MUG_C3, MUG_D1, MUG_D2 and POSTIT
    And I should not see product MUG_A1, MUG_A4, MUG_C1 and MUG_C4
    And the rows "MUG_B1, MUG_B2, MUG_D1 and MUG_D2" should be checked
    And the rows "MUG_A2, MUG_A3, MUG_C2, MUG_C3 and POSTIT" should not be checked
    And I should see the columns In group, SKU, Color, Label, Family, Status, Complete, Created at and Updated at

  Scenario: Successfully display the product datagrid when I edit a variant group with 2 axes
    Given I am on the "POSTIT" variant group page
    Then the grid should contain 3 elements
    And I should see products MUG_A2, MUG_C2 and POSTIT
    And I should not see products MUG_A1, MUG_A3, MUG_A4, MUG_B1, MUG_B2, MUG_C1, MUG_C3, MUG_C4, MUG_D1 and MUG_D2
    And the rows "MUG_A2, MUG_C2 and POSTIT" should not be checked
    And I should see the columns In group, SKU, Color, Size, Label, Family, Status, Complete, Created at and Updated at
