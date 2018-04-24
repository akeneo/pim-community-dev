Feature: Update groups fields
  In order to update products
  As an internal process or any user
  I need to be able to add a group in the groups field of a product

  Scenario: Successfully update the groups field
    Given a "apparel" catalog configuration
    And the following products:
      | sku                        | groups  |
      | add_one_when_empty         |         |
      | add_two_when_empty         |         |
      | add_nothing_when_empty     |         |
      | add_one_when_not_empty     | similar |
      | add_two_when_not_empty     | similar |
      | add_nothing_when_not_empty | similar |
    Then I should get the following products after apply the following updater to it:
      | product                    | actions                                                                 | result                                     |
      | add_one_when_empty         | [{"type": "add_data", "field": "groups", "data": ["related"]}]          | {"groups": ["related"]}                    |
      | add_two_when_empty         | [{"type": "add_data", "field": "groups", "data": ["related","upsell"]}] | {"groups": ["related","upsell"]}           |
      | add_nothing_when_empty     | [{"type": "add_data", "field": "groups", "data": []}]                   | {"groups": []}                             |
      | add_one_when_not_empty     | [{"type": "add_data", "field": "groups", "data": ["related"]}]          | {"groups": ["related","similar"]}          |
      | add_two_when_not_empty     | [{"type": "add_data", "field": "groups", "data": ["related","upsell"]}] | {"groups": ["related","similar","upsell"]} |
      | add_nothing_when_not_empty | [{"type": "add_data", "field": "groups", "data": []}]                   | {"groups": ["similar"]}                    |
