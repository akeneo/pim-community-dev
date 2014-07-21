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
      | code  | label | type         | useableAsGridColumn | useableAsGridFilter |
      | color | Color | simpleselect | 1                   | 1                   |
      | size  | Size  | simpleselect | 1                   | 1                   |
    And the following "color" attribute options: Yellow, Blue, Green and Red
    And the following "size" attribute options: XS, S, M, L and XL
    And the following products:
      | sku    | family    | color | size |
      | MUG_1  | mug       | Red   | XL   |
      | MUG_2  | mug       | Green |      |
      | MUG_3  | mug       |       | S    |
      | POSTIT | furniture | Blue  | M    |
    And the following product groups:
      | code   | label      | attributes  | type    |
      | MUG    | MUG Akeneo | color       | VARIANT |
      | POSTIT | Postit     | color, size | VARIANT |
    And I am logged in as "Julia"

  Scenario: Successfully display the product datagrid when I edit a variant group
    Given I am on the "MUG" variant group page
    Then the grid should contain 3 elements
    And I should see products MUG_1, MUG_2 and POSTIT
    And I should not see product MUG_3
    And I should see the columns In group, SKU, Color, Label, Family, Status, Complete, Created at and Updated at

  Scenario: Successfully display the product datagrid when I edit a variant group with 2 axes
    Given I am on the "POSTIT" variant group page
    Then the grid should contain 2 elements
    And I should see products MUG_1 and POSTIT
    And I should not see products MUG_2 and MUG_3
    And I should see the columns In group, SKU, Color, Size, Label, Family, Status, Complete, Created at and Updated at
