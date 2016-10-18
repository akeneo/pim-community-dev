Feature: Update price fields
  In order to update products
  As an internal process or any user
  I need to be able to copy a price field of a product

  Scenario: Successfully update a price field
    Given a "apparel" catalog configuration
    And the following attributes:
      | code         | type   |
      | price_winter | prices |
      | price_summer | prices |
    And the following products:
      | sku     | price_winter   |
      | AKN_MUG | 5 GBP, 10 EUR, 15 USD |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                           | result                                                                                                                                                                            |
      | AKN_MUG | [{"type": "copy_data", "from_field": "price_winter", "to_field": "price_summer"}] | {"values": {"price_summer": [{"locale": null, "scope": null, "data": [{"amount": 5, "currency": "GBP"}, {"amount": 10, "currency": "EUR"}, {"amount": 15, "currency": "USD"}]}]}} |
