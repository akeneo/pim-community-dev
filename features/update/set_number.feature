Feature: Update number fields
  In order to update products
  As an internal process or any user
  I need to be able to update a number field of a product

  Scenario: Successfully update a number field
    Given a "default" catalog configuration
    And the following attributes:
      | code         | type   | localizable | scopable |
      | item_count   | number | yes         | no       |
      | car_count    | number | no          | yes      |
      | wheel_count  | number | yes         | yes      |
      | screen_count | number | no          | no       |
    And the following products:
      | sku                     |
      | lot_of_items            |
      | no_items                |
      | small_amount_of_items   |
      | average_amount_of_items |
      | reverted                |
    Then I should get the following products after apply the following updater to it:
      | product                 | actions                                                                                                                                                                               | result                                                                           |
      | lot_of_items            | [{"type": "set_data", "field": "item_count", "data": 12, "locale": "fr_FR", "scope": null}]                                                                                           | {"values": {"item_count": [{"locale": "fr_FR", "scope": null, "data": 12}]}}     |
      | no_items                | [{"type": "set_data", "field": "car_count", "data": 0, "locale": null, "scope": "mobile"}]                                                                                            | {"values": {"car_count": [{"locale": null, "scope": "mobile", "data": 0}]}}      |
      | small_amount_of_items   | [{"type": "set_data", "field": "wheel_count", "data": 3, "locale": "fr_FR", "scope": "mobile"}]                                                                                       | {"values": {"wheel_count": [{"locale": "fr_FR", "scope": "mobile", "data": 3}]}} |
      | average_amount_of_items | [{"type": "set_data", "field": "screen_count", "data": 7, "locale": null, "scope": null}]                                                                                             | {"values": {"screen_count": [{"locale": null, "scope": null, "data": 7}]}}       |
      | reverted                | [{"type": "set_data", "field": "screen_count", "data": 7, "locale": null, "scope": null}, {"type": "set_data", "field": "screen_count", "data": null, "locale": null, "scope": null}] | {"values": {"screen_count": [{"locale": null, "scope": null, "data": null}]}}    |
