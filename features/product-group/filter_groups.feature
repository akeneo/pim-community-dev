@javascript
Feature: Filter product groups
  In order to filter product groups in the catalog
  As a user
  I need to be able to filter product groups in the catalog

  Scenario: Successfully filter product groups
    Given the "default" catalog configuration
    And the following attributes:
      | code  | label | type                     |
      | color | Color | pim_catalog_simpleselect |
      | size  | Size  | pim_catalog_simpleselect |
    And the following product groups:
      | code         | label      | attributes | type    |
      | MUG          | Mug Akeneo | color      | VARIANT |
      | CROSS_SELL_1 | Cross Sell |            | X_SELL  |
      | CROSS_SELL_2 | Relational |            | X_SELL  |
    And I am logged in as "admin"
    And I am on the product groups page
    Then the grid should contain 2 elements
    And I should see groups CROSS_SELL_1 and CROSS_SELL_2
    And I should be able to use the following filters:
      | filter | value  | result                        |
      | Code   | 2      | CROSS_SELL_2                  |
      | Label  | Cross  | CROSS_SELL_1                  |
      | Type   | X_SELL | CROSS_SELL_1 and CROSS_SELL_2 |
