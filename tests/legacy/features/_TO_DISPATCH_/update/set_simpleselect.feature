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
      | product  | actions                                                                                                                                                                     | result                                                                    |
      | red      | [{"type": "set_data", "field": "color", "data": "red", "locale": null, "scope": null}]                                                                                      | {"values": {"color": [{"locale": null, "scope": null, "data": "red"}]}}   |
      | black    | [{"type": "set_data", "field": "color", "data": "black", "locale": null, "scope": null}]                                                                                    | {"values": {"color": [{"locale": null, "scope": null, "data": "black"}]}} |
      | reverted | [{"type": "set_data", "field": "color", "data": null, "locale": null, "scope": null}]                                                                                       | {"values": {"color": [{"locale": null, "scope": null, "data": ""}]}}      |
      | reverted | [{"type": "set_data", "field": "color", "data": "red", "locale": null, "scope": null}, {"type": "set_data", "field": "color", "data": null, "locale": null, "scope": null}] | {"values": {"color": [{"locale": null, "scope": null, "data": ""}]}}      |
