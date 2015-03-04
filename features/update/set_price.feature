Feature: Update price fields
  In order to update products
  As an internal process or any user
  I need to be able to update a price field of a product

  Scenario: Successfully update a price field
    Given a "default" catalog configuration
    And the following attributes:
      | code   | type   | localizable | scopable |
      | price  | prices | yes         | no       |
    And the following products:
      | sku  |
      | BOX1 |
      | BOX2 |
      | BOX3 |
      | BOX4 |
      | BOX5 |
      | BOX6 |
      | BOX7 |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                                                                                                          | result                                                                                                    |
      | BOX1    | [{"type": "set_value", "field": "price", "value": [{"data": "12.4", "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                      | {"values": {"price": [{"locale": "fr_FR", "scope": null, "value": [{"data": 12.4, "currency": "EUR"}]}]}} |
      | BOX2    | [{"type": "set_value", "field": "price", "value": [{"data": 5, "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                           | {"values": {"price": [{"locale": "fr_FR", "scope": null, "value": [{"data": 5, "currency": "EUR"}]}]}}    |
      | BOX3    | [{"type": "set_value", "field": "price", "value": [{"data": 5.3, "currency": "EUR"}], "locale": "fr_FR", "scope": null}]                                                                                                                         | {"values": {"price": [{"locale": "fr_FR", "scope": null, "value": [{"data": 5.3, "currency": "EUR"}]}]}}  |
      | BOX4    | [{"type": "set_value", "field": "price", "value": [{"data": "5", "currency": "USD"}], "locale": "fr_FR", "scope": null}]                                                                                                                         | {"values": {"price": [{"locale": "fr_FR", "scope": null, "value": [{"data": 5, "currency": "USD"}]}]}}    |
      | BOX4    | [{"type": "set_value", "field": "price", "value": [{"data": "5", "currency": "EUR"}], "locale": "fr_FR", "scope": null}, {"type": "set_value", "field": "price", "value": [{"data": "5", "currency": "USD"}], "locale": "fr_FR", "scope": null}] | {"values": {"price": [{"locale": "fr_FR", "scope": null, "value": [{"data": 5, "currency": "USD"}]}]}}    |
      | BOX6    | [{"type": "set_value", "field": "price", "value": [{"data": "5", "currency": "EUR"}], "locale": "fr_FR", "scope": null}, {"type": "set_value", "field": "price", "value": [], "locale": "fr_FR", "scope": null}]                                 | {"values": {"price": [{"locale": "fr_FR", "scope": null, "value": []}]}}                                  |
      | BOX7    | [{"type": "set_value", "field": "price", "value": [{"data": "5", "currency": "EUR"}], "locale": "fr_FR", "scope": null}, {"type": "set_value", "field": "price", "value": [], "locale": "fr_FR", "scope": null}]                                 | {"values": {"price": [{"locale": "fr_FR", "scope": null, "value": []}]}}                                  |
