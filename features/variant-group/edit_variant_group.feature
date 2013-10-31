@javascript
Feature: Edit a variant group
  In order to manage existing variant groups for the catalog
  As a user
  I need to be able to edit a variant group

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

  Scenario: Successfully display the edit view for a variant group
    Given I am on the "MUG" variant group page
    And I visit the "Properties" tab
    Then I should see the Code and Axis fields
    And the fields Code and Axis should be disabled

  Scenario: Successfully edit a variant group
    Given I am on the "MUG" variant group page
    And I visit the "Properties" tab
    When I fill in the following information:
      | English (United States) | My Mug |
    And I press the "Save" button
    Then I should see "My Mug"

  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "MUG" variant group page
    And I visit the "Properties" tab
    When I fill in the following information:
      | English (United States) | Mug |
    Then I should see "There are unsaved changes."

  Scenario: Successfully have a confirmation popup when I change page with unsaved changes
    Given I am on the "MUG" variant group page
    And I visit the "Properties" tab
    When I fill in the following information:
      | English (United States) | Mug |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                          |
      | content | You will lose changes to the variant group if you leave this page. |
