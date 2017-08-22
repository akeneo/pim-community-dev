Feature: Remove price fields
  In order to update products
  As an internal process or any user
  I need to be able to remove a price field of a product

  Scenario: Successfully remove a price field
    Given a "default" catalog configuration
    And the following attributes:
      | code  | type                         | localizable | scopable | group | decimals_allowed |
      | price | pim_catalog_price_collection | 1           | 0        | other | 0                |
    And the following products:
      | sku  | price-fr_FR   | price-en_US   |
      | BOX1 | 5 EUR, 5 USD  | 5 EUR, 5 USD  |
      | BOX2 | 10 EUR, 5 USD | 10 EUR, 5 USD |
      | BOX3 |               |               |
      | BOX4 | 5 EUR, 5 USD  | 5 EUR, 5 USD  |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                                                                                                                | result                                                                                                                                                                                                                          |
      | BOX1    | [{"type": "remove_data", "field": "price", "data": [{"amount": "", "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                             | {"values": {"price": [{"locale": "en_US", "scope": null, "data": [{"amount": "5", "currency": "EUR"}, {"amount": "5", "currency": "USD"}]}, {"locale": "fr_FR", "scope": null, "data": [{"amount": "5", "currency": "USD"}]}]}} |
      | BOX2    | [{"type": "remove_data", "field": "price", "data": [{"amount": null, "currency": "EUR"}, {"amount": null, "currency": "USD"}], "locale": "fr_FR", "scope": null}]                                                                                      | {"values": {"price": [{"locale": "en_US", "scope": null, "data": [{"amount": "10", "currency": "EUR"}, {"amount": "5", "currency": "USD"}]}, {"locale": "fr_FR", "scope": null, "data": []}]}}                                  |
      | BOX3    | [{"type": "remove_data", "field": "price", "data": [{"amount": 5.3, "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                            | {"values": []}                                                                                                                                                                                                                  |
      | BOX4    | [{"type": "remove_data", "field": "price", "data": [{"amount": "5", "currency": "USD"}], "locale": "fr_FR", "scope": null}, {"type": "remove_data", "field": "price", "data": [{"amount": "5", "currency": "EUR"}], "locale": "en_US", "scope": null}] | {"values": {"price": [{"locale": "fr_FR", "scope": null, "data": [{"amount": "5", "currency": "EUR"}]}, {"locale": "en_US", "scope": null, "data": [{"amount": "5", "currency": "USD"}]}]}}                                     |
