Feature: Update variant group fields
  In order to update products
  As an internal process or any user
  I need to be able to update the variant group field of a product

  Scenario: Successfully update the variant group field
    Given a "footwear" catalog configuration
    And the following products:
      | sku                              |
      | tshirt1                          |
      | tshirt2                          |
    And the following product groups:
      | code    | label         | type    | axis |
      | TSHIRT1 | First tshirt  | VARIANT | size |
      | TSHIRT2 | Second tshirt | VARIANT | size |
      | TSHIRT3 | Third tshirt  | VARIANT | size |
    Then I should get the following products after apply the following updater to it:
      | product             | actions                                                                                                                                | result                       |
      | tshirt1             | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT1"}]                                                                    | {"variant_group": "TSHIRT1"} |
      | tshirt1             | [{"type": "set_data", "field": "variant_group", "data": null}]                                                                         | {"variant_group": ""}        |
      | tshirt2             | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT2"}]                                                                    | {"variant_group": "TSHIRT2"} |
      | tshirt2             | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT1"}, {"type": "set_data", "field": "variant_group", "data": "TSHIRT3"}] | {"variant_group": "TSHIRT3"} |

  Scenario: Successfully update the variant group field and the group field
    Given a "footwear" catalog configuration
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
      | code    | label         | type    | axis |
      | TSHIRT1 | First tshirt  | VARIANT | size |
      | TSHIRT2 | Second tshirt | VARIANT | size |
      | TSHIRT3 | Third tshirt  | VARIANT | size |
      | PACK1   | First pack    | PACK    |      |
      | PACK2   | Second pack   | PACK    |      |
      | PACK3   | Third pack    | PACK    |      |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                  | result                                                     |
      | tshirt1 | [{"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2", "TSHIRT1"]}]                                                         | {"groups": ["PACK1", "PACK2"], "variant_group": "TSHIRT1"} |
      | tshirt2 | [{"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2"]}, {"type": "set_data", "field": "variant_group", "data": null}]      | {"groups": ["PACK1", "PACK2"]}                             |
      | tshirt4 | [{"type": "set_data", "field": "variant_group", "data": "TSHIRT3"}, {"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2"]}] | {"groups": ["PACK1", "PACK2"]}                             |
