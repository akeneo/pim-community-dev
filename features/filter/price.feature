Feature: Filter on price attributes
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by price attributes

  Scenario: Successfully filter on price attributes
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | price          |
      | BOOTBXS | 12 EUR, 10 USD |
      | BOOTWXS | 35 EUR, 30 USD |
      | BOOTBS  | 44 EUR, 40 USD |
      | BOOTBL  | 10 EUR, 11 USD |
      | BOOTRXS | 10 EUR         |
    Then I should get the following results for the given filters:
      | filter                                                                               | result                                                |
      | [{"field":"price", "operator":"=",     "value": {"data": 44, "currency": "EUR"}}]    | ["BOOTBS"]                                            |
      | [{"field":"price", "operator":"<",     "value": {"data": 25, "currency": "EUR"}}]    | ["BOOTBL", "BOOTBXS", "BOOTRXS"]                      |
      | [{"field":"price", "operator":"<=",    "value": {"data": 44, "currency": "EUR"}}]    | ["BOOTWXS", "BOOTBS", "BOOTBL", "BOOTBXS", "BOOTRXS"] |
      | [{"field":"price", "operator":">=",    "value": {"data": 35, "currency": "EUR"}}]    | ["BOOTBS", "BOOTWXS"]                                 |
      | [{"field":"price", "operator":">",     "value": {"data": 30, "currency": "USD"}}]    | ["BOOTBS"]                                            |
      | [{"field":"price", "operator":"EMPTY", "value": {"data": null, "currency": "EUR"} }] | []                                                    |
      | [{"field":"price", "operator":">",     "value": {"data": 10, "currency": "USD"}}, {"field":"price", "operator":"<=",     "value": {"data": 35, "currency": "EUR"}}] | ["BOOTWXS", "BOOTBL"] |
