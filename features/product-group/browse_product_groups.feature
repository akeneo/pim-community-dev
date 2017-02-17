@javascript
Feature: Browse product groups
  In order to list the existing product groups for the catalog
  As a product manager
  I need to be able to see product groups

  Scenario: Successfully view, sort and filter product groups
    Given the "default" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | group |
      | multi | Multi       | pim_catalog_multiselect  | other |
      | color | Color       | pim_catalog_simpleselect | other |
      | size  | Size        | pim_catalog_simpleselect | other |
    And the following variant groups:
      | code          | label-en_US    | axis       | type    |
      | tshirt_akeneo | Akeneo T-Shirt | size,color | VARIANT |
      | mug_akeneo    | Akeneo Mug     | color      | VARIANT |
    And the following product groups:
      | code         | label-en_US | type   |
      | CROSS_SELL_1 | Cross Sell  | X_SELL |
      | CROSS_SELL_2 | Relational  | X_SELL |
    And I am logged in as "Julia"
    And I am on the product groups page
    Then the grid should contain 2 elements
    And I should see the columns Code, Label and Type
    And I should see groups CROSS_SELL_1 and CROSS_SELL_2
    And the rows should be sorted ascending by Code
    And I should be able to sort the rows by Code, Label and Type
    And I should be able to use the following filters:
      | filter | operator | value      | result                        |
      | code   | contains | 2          | CROSS_SELL_2                  |
      | label  | contains | Cross      | CROSS_SELL_1                  |
      | type   |          | Cross sell | CROSS_SELL_1 and CROSS_SELL_2 |
