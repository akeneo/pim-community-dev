Feature: Filter on boolean
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by boolean

  Scenario: Successfully filter on boolean
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | enabled | handmade |
      | BOOTBXS | 1       | 1        |
      | BOOTWXS | 0       | 0        |
      | BOOTBS  | 1       |          |
      | BOOTBL  | 1       | 1        |
      | BOOTRXS | 0       | 0        |
    Then I should get the following results for the given filters:
      | filter                                                 | result                          |
      | [{"field":"enabled", "operator":"=", "value": true}]   | ["BOOTBXS", "BOOTBL", "BOOTBS"] |
      | [{"field":"enabled", "operator":"=", "value": false}]  | ["BOOTWXS", "BOOTRXS"]          |
      | [{"field":"handmade", "operator":"=", "value": true}]  | ["BOOTBXS", "BOOTBL"]           |
      | [{"field":"handmade", "operator":"=", "value": false}] | ["BOOTWXS", "BOOTRXS"]          |
      | [{"field":"enabled", "operator":"=", "value": true}, {"field":"handmade", "operator":"=", "value": true}] | ["BOOTBXS", "BOOTBL"]  |
