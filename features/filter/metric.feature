Feature: Filter on metric attributes
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by metric attributes

  Scenario: Successfully filter on metric attributes
    Given a "footwear" catalog configuration
    And the following products:
      | sku       | length        |
      | SUSHIROLL | 12 CENTIMETER |
      | OBELISK   | 35 CENTIMETER |
      | CASSAVA   | 44 CENTIMETER |
      | PEN       | 0 CENTIMETER  |
      | FINGER    |               |
    Then I should get the following results for the given filters:
      | filter                                                                                | result                                     |
      | [{"field":"length", "operator":"=",     "value": {"data": 44, "unit": "CENTIMETER"}}] | ["CASSAVA"]                                 |
      | [{"field":"length", "operator":"<",     "value": {"data": 25, "unit": "CENTIMETER"}}] | ["PEN", "SUSHIROLL"]                      |
      | [{"field":"length", "operator":"<=",    "value": {"data": 44, "unit": "CENTIMETER"}}] | ["OBELISK", "CASSAVA", "PEN", "SUSHIROLL"] |
      | [{"field":"length", "operator":">=",    "value": {"data": 35, "unit": "CENTIMETER"}}] | ["CASSAVA", "OBELISK"]                      |
      | [{"field":"length", "operator":">",     "value": {"data": 35, "unit": "CENTIMETER"}}] | ["CASSAVA"]                                 |
      | [{"field":"length", "operator":"<=", "value": {"data": 44, "unit": "CENTIMETER"}}, {"field":"length", "operator":">=", "value": {"data": 12, "unit": "CENTIMETER"}}] | ["OBELISK", "CASSAVA", "SUSHIROLL"] |
      | [{"field":"length", "operator":"EMPTY", "value": null}] | ["FINGER" ]                              |
