Feature: Create a draft with a price fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a price field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tshirts    |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following products after apply the following updater to it:
      | product       | actions                                                                                                                                             | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "price", "data": [{"data": 4, "currency": "EUR"}, {"data": "5", "currency": "USD"}], "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product        | username | result                                                                 |
      | akeneo_tshirt  | Mary     | {"values": {"price": [{"locale": null, "scope": null, "value": [{"data": 4, "currency": "EUR"}, {"data": "5", "currency": "USD"}]}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | price | |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute | value  |
      | akeneo_tshirt | price     | 12 EUR, 15 USD |
    Then I should get the following products after apply the following updater to it:
      | product       | actions                                                                                                                                             | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "price", "data": [{"data": 4, "currency": "EUR"}, {"data": "5", "currency": "USD"}], "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product        | username | result                                                                                                |
      | akeneo_tshirt  | Mary     | {"values": {"price": [{"locale": null, "scope": null, "value": [{"data": 4, "currency": "EUR"}, {"data": "5", "currency": "USD"}]}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | price | 15.00 USD |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute | value |
      | akeneo_tshirt | price     | 5 USD |
    Then I should get the following products after apply the following updater to it:
      | product       | actions                                                                                                                | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "price", "data": [{"data": "5.00", "currency": "USD"}], "locale": null, "scope": null}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
