@javascript
Feature: Sort available products for a variant group
  In order to easily browse products inside a variant group
  As a product manager
  I need to be able to sort products in a variant group

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | useable_as_grid_filter | group |
      | color | Color       | pim_catalog_simpleselect | 1                      | other |
      | size  | Size        | pim_catalog_simpleselect | 1                      | other |
    And the following families:
      | code      | label-en_US | attributes |
      | mug       | Mug         | color,size |
      | furniture | Furniture   | color,size |
    And the following "color" attribute options: Yellow, Blue, Green and Red
    And the following "size" attribute options: XS, S, M, L and XL
    And the following variant groups:
      | code   | label-en_US | axis       | type    |
      | POSTIT | Postit      | color,size | VARIANT |
    And the following products:
      | sku    | family    | color | size | groups |
      | MUG_1  | mug       | Red   | M    |        |
      | POSTIT | furniture | Blue  | XL   | POSTIT |
    And I am logged in as "Julia"

  Scenario: Successfully sort products
    Given I am on the "POSTIT" variant group page
    Then the rows should be sorted descending by In group
    And I should be able to sort the rows by In group, SKU, Color, Size, Family, Created at and Updated at
