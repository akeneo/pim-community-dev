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
      | BOOTYXS | EUR            |
      | BOOTYXL | EUR, 2 USD     |
    Then I should get the following results for the given filters:
      | filter                                                                                                                                                          | result                                                |
      | [{"field":"price", "operator":"=",         "value": {"amount": 44, "currency": "EUR"}}]                                                                         | ["BOOTBS"]                                            |
      | [{"field":"price", "operator":"<",         "value": {"amount": 25, "currency": "EUR"}}]                                                                         | ["BOOTBL", "BOOTBXS", "BOOTRXS"]                      |
      | [{"field":"price", "operator":"<=",        "value": {"amount": 44, "currency": "EUR"}}]                                                                         | ["BOOTWXS", "BOOTBS", "BOOTBL", "BOOTBXS", "BOOTRXS"] |
      | [{"field":"price", "operator":">=",        "value": {"amount": 35, "currency": "EUR"}}]                                                                         | ["BOOTBS", "BOOTWXS"]                                 |
      | [{"field":"price", "operator":">",         "value": {"amount": 30, "currency": "USD"}}]                                                                         | ["BOOTBS"]                                            |
      | [{"field":"price", "operator":"EMPTY",     "value": {"amount": null, "currency": "EUR"} }]                                                                      | ["BOOTYXS", "BOOTYXL"]                                |
      | [{"field":"price", "operator":"NOT EMPTY", "value": {"amount": null, "currency": "EUR"} }]                                                                      | ["BOOTWXS", "BOOTBS", "BOOTBL", "BOOTBXS", "BOOTRXS"] |
      | [{"field":"price", "operator":"!=",        "value": {"amount": 10, "currency": "EUR"} }]                                                                        | ["BOOTWXS", "BOOTBS", "BOOTBXS"]                      |
      | [{"field":"price", "operator":">", "value": {"amount": 10, "currency": "USD"}}, {"field":"price", "operator":"<=", "value": {"amount": 35, "currency": "EUR"}}] | ["BOOTWXS", "BOOTBL"]                                 |
