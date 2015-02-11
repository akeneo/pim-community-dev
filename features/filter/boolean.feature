Feature: Filter on boolean
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by boolean

  Scenario: Successfully filter on boolean
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | enabled | handmade |
      | BOOTBXS | true    | true     |
      | BOOTWXS | false   | false    |
      | BOOTBS  | true    |          |
      | BOOTBL  | true    | true     |
      | BOOTRXS | false   | false    |
    Then I should get the following results for the given filters:
      | filter                                                 | result                          |
      | [{"field":"enabled", "operator":"=", "value": true}]   | ["BOOTBXS", "BOOTBL", "BOOTBS"] |
      | [{"field":"enabled", "operator":"=", "value": false}]  | ["BOOTWXS", "BOOTRXS"]          |
      | [{"field":"handmade", "operator":"=", "value": true}]  | ["BOOTBXS", "BOOTBL"]           |
      | [{"field":"handmade", "operator":"=", "value": false}] | ["BOOTWXS", "BOOTRXS"]          |
      | [{"field":"enabled", "operator":"=", "value": true}, {"field":"handmade", "operator":"=", "value": true}] | ["BOOTBXS", "BOOTBL"]  |
