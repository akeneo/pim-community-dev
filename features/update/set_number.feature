Feature: Update boolean fields
  In order to update products
  As an internal process or any user
  I need to be able to update a boolean field of a product

  Scenario: Successfully update a boolean field
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
      | product                 | actions                                                                                                                                                                                   | result                                                                            |
      | lot_of_items            | [{"type": "set_value", "field": "item_count", "value": 12, "locale": "fr_FR", "scope": null}]                                                                                             | {"values": {"item_count": [{"locale": "fr_FR", "scope": null, "value": 12}]}}     |
      | no_items                | [{"type": "set_value", "field": "car_count", "value": 0, "locale": null, "scope": "mobile"}]                                                                                              | {"values": {"car_count": [{"locale": null, "scope": "mobile", "value": 0}]}}      |
      | small_amount_of_items   | [{"type": "set_value", "field": "wheel_count", "value": 3, "locale": "fr_FR", "scope": "mobile"}]                                                                                         | {"values": {"wheel_count": [{"locale": "fr_FR", "scope": "mobile", "value": 3}]}} |
      | average_amount_of_items | [{"type": "set_value", "field": "screen_count", "value": 7, "locale": null, "scope": null}]                                                                                               | {"values": {"screen_count": [{"locale": null, "scope": null, "value": 7}]}}       |
      | reverted                | [{"type": "set_value", "field": "screen_count", "value": 7, "locale": null, "scope": null}, {"type": "set_value", "field": "screen_count", "value": null, "locale": null, "scope": null}] | {"values": {"screen_count": [{"locale": null, "scope": null, "value": null}]}}    |
