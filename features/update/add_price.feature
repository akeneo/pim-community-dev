Feature: Update price fields
  In order to update products
  As an internal process or any user
  I need to be able to update a price field of a product

  Scenario: Successfully update a price field
    Given a "default" catalog configuration
    And the following attributes:
      | code  | type                         | localizable | scopable | group | decimals_allowed |
      | price | pim_catalog_price_collection | 1           | 0        | other | 1                |
    And the following products:
      | sku  | price-fr_FR  |
      | BOX1 |              |
      | BOX2 |              |
      | BOX3 |              |
      | BOX4 | 5 EUR        |
      | BOX5 | 5 EUR, 5 USD |
      | BOX6 | 5 EUR, 5 USD |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                                                                                                            | result                                                                                                                                          |
      | BOX1    | [{"type": "add_data", "field": "price", "data": [{"amount": "12.4", "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                        | {"values": {"price": [{"locale": "fr_FR", "scope": null, "data": [{"amount": 12.4, "currency": "EUR"}]}]}}                                      |
      | BOX2    | [{"type": "add_data", "field": "price", "data": [{"amount": 5, "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                             | {"values": {"price": [{"locale": "fr_FR", "scope": null, "data": [{"amount": 5, "currency": "EUR"}]}]}}                                         |
      | BOX3    | [{"type": "add_data", "field": "price", "data": [{"amount": 5.3, "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                           | {"values": {"price": [{"locale": "fr_FR", "scope": null, "data": [{"amount": 5.3, "currency": "EUR"}]}]}}                                       |
      | BOX4    | [{"type": "add_data", "field": "price", "data": [{"amount": "5", "currency": "USD"}], "locale": "fr_FR", "scope": null}]                                                                                                                           | {"values": {"price": [{"locale": "fr_FR", "scope": null, "data": [{"amount": 5, "currency": "EUR"}, {"amount": 5, "currency": "USD"}]}]}}       |
      | BOX5    | [{"type": "add_data", "field": "price", "data": [{"amount": null, "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                          | {"values": {"price": [{"locale": "fr_FR", "scope": null, "data": [{"amount": null, "currency": "EUR"}, {"amount": 5, "currency": "USD"}]}]}}    |
      | BOX6    | [{"type": "add_data", "field": "price", "data": [{"amount": null, "currency": "EUR"}], "locale": "fr_FR", "scope": null}, {"type": "add_data", "field": "price", "data": [{"amount": null, "currency": "USD"}], "locale": "fr_FR", "scope": null}] | {"values": {"price": [{"locale": "fr_FR", "scope": null, "data": [{"amount": null, "currency": "EUR"}, {"amount": null, "currency": "USD"}]}]}} |
