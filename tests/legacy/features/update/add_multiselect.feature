Feature: Update multi select fields
  In order to update products
  As an internal process or any user
  I need to be able to add an option in a multi select field of a product

  Scenario: Successfully update a multi select field
    Given a "apparel" catalog configuration
    And the following products:
      | sku                        | additional_colors |
      | empty_add_blue             |                   |
      | empty_add_blue_and_red     |                   |
      | empty_add_nothing          |                   |
      | not_empty_add_blue         | additional_black  |
      | not_empty_add_blue_and_red | additional_black  |
      | not_empty_add_nothing      | additional_black  |
      | not_empty_add_existing     | additional_black  |
    Then I should get the following products after apply the following updater to it:
      | product                    | actions                                                                                                                            | result                                                                                                                                 |
      | empty_add_blue             | [{"type": "add_data", "field": "additional_colors", "data": ["additional_blue"], "locale": null, "scope": null}]                   | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_blue"]}]}}                                      |
      | empty_add_blue_and_red     | [{"type": "add_data", "field": "additional_colors", "data": ["additional_blue", "additional_red"], "locale": null, "scope": null}] | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_blue", "additional_red"]}]}}                    |
      | empty_add_nothing          | [{"type": "add_data", "field": "additional_colors", "data": [], "locale": null, "scope": null}]                                    | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": []}]}}                                                       |
      | not_empty_add_blue         | [{"type": "add_data", "field": "additional_colors", "data": ["additional_blue"], "locale": null, "scope": null}]                   | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_black","additional_blue"]}]}}                   |
      | not_empty_add_blue_and_red | [{"type": "add_data", "field": "additional_colors", "data": ["additional_blue", "additional_red"], "locale": null, "scope": null}] | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_black","additional_blue", "additional_red"]}]}} |
      | not_empty_add_nothing      | [{"type": "add_data", "field": "additional_colors", "data": [], "locale": null, "scope": null}]                                    | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_black"]}]}}                                     |
      | not_empty_add_existing     | [{"type": "add_data", "field": "additional_colors", "data": ["additional_black"], "locale": null, "scope": null}]                  | {"values": {"additional_colors": [{"locale": null, "scope": null, "data": ["additional_black"]}]}}                                     |
