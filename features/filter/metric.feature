Feature: Filter on metric attributes
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by metric attributes

  Scenario: Successfully filter on metric attributes
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | length        |
      | BOOTBXS | 12 CENTIMETER |
      | BOOTWXS | 35 CENTIMETER |
      | BOOTBS  | 44 CENTIMETER |
      | BOOTBL  | 0 CENTIMETER  |
      | BOOTRXS |               |
    Then I should get the following results for the given filters:
      | filter                                                                                | result                                     |
      | [{"field":"length", "operator":"=",     "value": {"data": 44, "unit": "CENTIMETER"}}] | ["BOOTBS"]                                 |
      | [{"field":"length", "operator":"<",     "value": {"data": 25, "unit": "CENTIMETER"}}] | ["BOOTBL", "BOOTBXS"]                      |
      | [{"field":"length", "operator":"<=",    "value": {"data": 44, "unit": "CENTIMETER"}}] | ["BOOTWXS", "BOOTBS", "BOOTBL", "BOOTBXS"] |
      | [{"field":"length", "operator":">=",    "value": {"data": 35, "unit": "CENTIMETER"}}] | ["BOOTBS", "BOOTWXS"]                      |
      | [{"field":"length", "operator":">",     "value": {"data": 35, "unit": "CENTIMETER"}}] | ["BOOTBS"]                                 |
      | [{"field":"length", "operator":"<=", "value": {"data": 44, "unit": "CENTIMETER"}}, {"field":"length", "operator":">=", "value": {"data": 12, "unit": "CENTIMETER"}}] | ["BOOTWXS", "BOOTBS", "BOOTBXS"] |
      | [{"field":"length", "operator":"EMPTY", "value": null}] | ["BOOTRXS"]                              |
