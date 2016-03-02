Feature: Filter on metric attributes
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by metric attributes

  Scenario: Successfully filter on metric attributes
    Given a "footwear" catalog configuration
    And the following products:
      | sku       | length        | volume              |
      | SUSHIROLL | 12 CENTIMETER |                     |
      | OBELISK   | 35 CENTIMETER |                     |
      | CASSAVA   | 44 CENTIMETER |                     |
      | PEN       | 0 CENTIMETER  |                     |
      | FINGER    |               |                     |
      | MOUTH     |               | 12 CUBIC_MILLIMETER |
      | SAUCEPAN  |               | 20 CUBIC_MILLIMETER |
      | POOL      |               | 15 CUBIC_METER      |
    Then I should get the following results for the given filters:
      | filter                                                                                                                                                               | result                                     |
      | [{"field":"length", "operator":"=",     "value": {"data": 44, "unit": "CENTIMETER"}}]                                                                                | ["CASSAVA"]                                |
      | [{"field":"length", "operator":"<",     "value": {"data": 25, "unit": "CENTIMETER"}}]                                                                                | ["PEN", "SUSHIROLL"]                       |
      | [{"field":"length", "operator":"<=",    "value": {"data": 44, "unit": "CENTIMETER"}}]                                                                                | ["OBELISK", "CASSAVA", "PEN", "SUSHIROLL"] |
      | [{"field":"length", "operator":">=",    "value": {"data": 35, "unit": "CENTIMETER"}}]                                                                                | ["CASSAVA", "OBELISK"]                     |
      | [{"field":"length", "operator":">",     "value": {"data": 35, "unit": "CENTIMETER"}}]                                                                                | ["CASSAVA"]                                |
      | [{"field":"length", "operator":"<=", "value": {"data": 44, "unit": "CENTIMETER"}}, {"field":"length", "operator":">=", "value": {"data": 12, "unit": "CENTIMETER"}}] | ["OBELISK", "CASSAVA", "SUSHIROLL"]        |
      | [{"field":"length", "operator":"EMPTY", "value": null}]                                                                                                              | ["FINGER", "MOUTH", "SAUCEPAN", "POOL"]    |
      | [{"field":"volume", "operator":"=", "value":{"data": 12, "unit":"CUBIC_MILLIMETER"}}]                                                                                | ["MOUTH"]                                  |
      | [{"field":"volume", "operator":"<", "value":{"data": 1, "unit":"CUBIC_METER"}}]                                                                                      | ["MOUTH", "SAUCEPAN"]                      |
      | [{"field":"volume", "operator":">", "value":{"data": 100, "unit":"CUBIC_MILLIMETER"}}]                                                                               | ["POOL"]                                   |
