Feature: Update multi select fields
  In order to update products
  As an internal process or any user
  I need to be able to update a multi select field of a product

  Scenario: Successfully update a multi select field
    Given a "apparel" catalog configuration
    And the following products:
      | sku             |
      | red_and_blue    |
      | red             |
      | black_and_white |
      | reverted        |
    Then I should get the following products after apply the following updater to it:
      | product         | actions                                                                                                                                                                                                                          | result                                                                                                                 |
      | red_and_blue    | [{"type": "set_data", "field": "additional_colors", "data": ["additional_red", "additional_blue"], "locale": null, "scope": null}]                                                                                               | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_blue", "additional_red"]}]}}    |
      | red             | [{"type": "set_data", "field": "additional_colors", "data": ["additional_red"], "locale": null, "scope": null}]                                                                                                                  | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_red"]}]}}                       |
      | red             | [{"type": "set_data", "field": "additional_colors", "data": ["additional_black"], "locale": null, "scope": null}, {"type": "set_data", "field": "additional_colors", "data": ["additional_red"], "locale": null, "scope": null}] | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_red"]}]}}                       |
      | black_and_white | [{"type": "set_data", "field": "additional_colors", "data": ["additional_black", "additional_white"], "locale": null, "scope": null}]                                                                                            | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_black", "additional_white"]}]}} |
      | reverted        | [{"type": "set_data", "field": "additional_colors", "data": [], "locale": null, "scope": null}]                                                                                                                                  | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": []}]}}                                       |
      | reverted        | [{"type": "set_data", "field": "additional_colors", "data": ["additional_red"], "locale": null, "scope": null}, {"type": "set_data", "field": "additional_colors", "data": [], "locale": null, "scope": null}]                   | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": []}]}}                                       |
