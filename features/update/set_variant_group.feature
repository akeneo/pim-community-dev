Feature: Update variant group fields
  In order to update products
  As an internal process or any user
  I need to be able to update the variant group field of a product

  Scenario: Successfully update the variant group field
    Given a "default" catalog configuration
    And the following products:
      | sku                              |
      | tshirt1                          |
      | tshirt2                          |
    And the following product groups:
      | code    | label         | type    |
      | TSHIRT1 | First tshirt  | VARIANT |
      | TSHIRT2 | Second tshirt | VARIANT |
      | TSHIRT3 | Third tshirt  | VARIANT |
    Then I should get the following products after apply the following updater to it:
      | product             | actions                                                                                                                                | result                  |
      | tshirt1             | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT1"}]                                                                    | {"groups": ["TSHIRT1"]} |
      | tshirt1             | [{"type": "set_data", "field": "variant_group", "data": null}]                                                                         | {"groups": []}          |
      | tshirt2             | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT2"}]                                                                    | {"groups": ["TSHIRT2"]} |
      | tshirt2             | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT1"}, {"type": "set_data", "field": "variant_group", "data": "TSHIRT3"}] | {"groups": ["TSHIRT3"]} |

  Scenario: Successfully update the variant group field and the group field
    Given a "default" catalog configuration
    And the following products:
      | sku                              |
      | tshirt1                          |
      | tshirt2                          |
      | tshirt3                          |
      | tshirt4                          |
    Given the following group type:
      | code |
      | PACK |
    And the following product groups:
      | code    | label         | type    |
      | TSHIRT1 | First tshirt  | VARIANT |
      | TSHIRT2 | Second tshirt | VARIANT |
      | TSHIRT3 | Third tshirt  | VARIANT |
      | PACK1   | First pack    | PACK    |
      | PACK2   | Second pack   | PACK    |
      | PACK3   | Third pack    | PACK    |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                                                                                                                                 | result                                    |
      | tshirt1 | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT1"}, {"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2"]}]                                                                                                                                | {"groups": ["PACK1", "PACK2", "TSHIRT1"]} |
      | tshirt2 | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT2"}, {"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2"]}, {"type": "set_data", "field": "variant_group", "data": null}]                                                                  | {"groups": ["PACK1", "PACK2"]}            |
      | tshirt3 | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT3"}, {"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2"]}, {"type": "set_data", "field": "variant_group", "data": "TSHIRT1"}, {"type": "set_data", "field": "groups", "data": ["PACK1"]}] | {"groups": ["PACK1","TSHIRT1"]}           |
      | tshirt4 | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT3"}]                                                                                                                                                                                                     | {"groups": ["TSHIRT3"]}                   |
      | tshirt4 | [{"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2"]}]                                                                                                                                                                                                   | {"groups": ["PACK1", "PACK2", "TSHIRT3"]} |
      | tshirt4 | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT1"}]                                                                                                                                                                                                     | {"groups": ["PACK1", "PACK2", "TSHIRT1"]} |
      | tshirt4 | [{"type": "set_data", "field": "groups", "data": ["PACK1"]}]                                                                                                                                                                                                            | {"groups": ["PACK1", "TSHIRT1"]}          |
