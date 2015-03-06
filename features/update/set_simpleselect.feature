Feature: Update simple select fields
  In order to update products
  As an internal process or any user
  I need to be able to update a simple select field of a product

  Scenario: Successfully update a simple select field
    Given a "apparel" catalog configuration
    And the following products:
      | sku      |
      | red      |
      | black    |
      | reverted |
    Then I should get the following products after apply the following updater to it:
      | product  | actions                                                                                                                                                                         | result                                                                     |
      | red      | [{"type": "set_value", "field": "color", "value": "red", "locale": null, "scope": null}]                                                                                        | {"values": {"color": [{"locale": null, "scope": null, "value": "red"}]}}   |
      | black    | [{"type": "set_value", "field": "color", "value": "black", "locale": null, "scope": null}]                                                                                      | {"values": {"color": [{"locale": null, "scope": null, "value": "black"}]}} |
      | reverted | [{"type": "set_value", "field": "color", "value": null, "locale": null, "scope": null}]                                                                                         | {"values": {"color": [{"locale": null, "scope": null, "value": ""}]}}      |
      | reverted | [{"type": "set_value", "field": "color", "value": "red", "locale": null, "scope": null}, {"type": "set_value", "field": "color", "value": null, "locale": null, "scope": null}] | {"values": {"color": [{"locale": null, "scope": null, "value": ""}]}}      |
