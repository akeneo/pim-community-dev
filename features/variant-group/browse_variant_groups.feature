@javascript
Feature: Browse variant groups
  In order to list the existing variant groups for the catalog
  As a product manager
  I need to be able to see variant groups

  Scenario: Successfully view, sort and filter variant groups
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label      | type         |
      | multi     | Multi      | multiselect  |
      | color     | Color      | simpleselect |
      | size      | Size       | simpleselect |
      | dimension | Dimensions | simpleselect |
    And the following product groups:
      | code           | label          | attributes  | type    |
      | tshirt_akeneo  | Akeneo T-Shirt | size, color | VARIANT |
      | mug            | Mug            | color       | VARIANT |
      | sticker_akeneo | Akeneo Sticker | dimension   | VARIANT |
      | cross_sell     | Cross Sell     |             | X_SELL  |
    And I am logged in as "Julia"
    And I am on the variant groups page
    Then the grid should contain 3 elements
    And I should see the columns Code, Label and Axis
    And I should see groups tshirt_akeneo, mug and sticker_akeneo
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code and label
    And I should be able to use the following filters:
      | filter | value  | result                           |
      | Code   | mug    | mug                              |
      | Label  | Akeneo | tshirt_akeneo and sticker_akeneo |
      | Axis   | Color  | tshirt_akeneo and mug            |
