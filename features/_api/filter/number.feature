Feature: Filter on price attributes
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by price attributes

  Scenario: Successfully filter on price attributes
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | number_in_stock |
      | BOOTBXS | 12              |
      | BOOTWXS | 35              |
      | BOOTBS  | 44              |
      | BOOTBL  | 0               |
      | BOOTRXS |                 |
    Then I should get the following results for the given filters:
      | filter                                                            | result                                     |
      | [{"field":"number_in_stock", "operator":"=",     "value": 44   }] | ["BOOTBS"]                                 |
      | [{"field":"number_in_stock", "operator":"<",     "value": 25   }] | ["BOOTBL", "BOOTBXS"]                      |
      | [{"field":"number_in_stock", "operator":"<=",    "value": 44   }] | ["BOOTWXS", "BOOTBS", "BOOTBL", "BOOTBXS"] |
      | [{"field":"number_in_stock", "operator":">=",    "value": 35   }] | ["BOOTBS", "BOOTWXS"]                      |
      | [{"field":"number_in_stock", "operator":">",     "value": 35   }] | ["BOOTBS"]                                 |
      | [{"field":"number_in_stock", "operator":"EMPTY", "value": null }] | ["BOOTRXS"]                                |
