@javascript
Feature: Browse product groups
  In order to list the existing product groups for the catalog
  As a product manager
  I need to be able to see product groups

  Scenario: Successfully view, sort and filter product groups
    Given the "default" catalog configuration
    And the following attributes:
      | code  | label | type         |
      | multi | Multi | multiselect  |
      | color | Color | simpleselect |
      | size  | Size  | simpleselect |
    And the following product groups:
      | code          | label          | attributes  | type    |
      | tshirt_akeneo | Akeneo T-Shirt | size, color | VARIANT |
      | mug_akeneo    | Akeneo Mug     | color       | VARIANT |
      | CROSS_SELL_1  | Cross Sell     |             | X_SELL  |
      | CROSS_SELL_2  | Relational     |             | X_SELL  |
    And I am logged in as "Julia"
    And I am on the product groups page
    Then the grid should contain 2 elements
    And I should see the columns Code, Label and Type
    And I should see groups CROSS_SELL_1 and CROSS_SELL_2
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label and type
    And I should be able to use the following filters:
      | filter | value  | result                        |
      | Code   | 2      | CROSS_SELL_2                  |
      | Label  | Cross  | CROSS_SELL_1                  |
      | Type   | X_SELL | CROSS_SELL_1 and CROSS_SELL_2 |
