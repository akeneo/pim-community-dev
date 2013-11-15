@javascript
Feature: Delete a variant group
  In order to manager variant groups for the catalog
  As a user
  I need to be able to delete variant groups

  Background:
    Given the "default" catalog configuration
    And the following families:
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
    And the following "color" attribute options: Yellow, Blue, Green and Red
    And the following "size" attribute options: XS, S, M, L and XL
    And the following product values:
      | product | attribute | value |
      | MUG_1   | color     | Red   |
      | MUG_1   | size      | XL    |
      | MUG_2   | color     | Green |
      | MUG_3   | size      | S     |
      | POSTIT  | color     | Blue  |
      | POSTIT  | size      | M     |
    And the following product groups:
      | code   | label      | attributes  | type    |
      | MUG    | MUG Akeneo | color       | VARIANT |
      | POSTIT | Postit     | color, size | VARIANT |
    And I am logged in as "admin"

  Scenario: Successfully delete a variant group from the grid
    Given I am on the variant groups page
    And I should see groups MUG
    When I click on the "Delete" action of the row which contains "MUG"
    And I confirm the deletion
    Then the grid should contain 1 element
    And I should not see group "MUG"

  Scenario: Successfully delete a variant group
    Given I edit the "POSTIT" variant group
    When I press the "Delete" button
    And I confirm the deletion
    Then the grid should contain 1 element
    And I should not see groups "POSTIT"
