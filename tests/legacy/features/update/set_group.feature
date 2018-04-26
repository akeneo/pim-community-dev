Feature: Update groups fields
  In order to update products
  As an internal process or any user
  I need to be able to update the groups field of a product

  Scenario: Successfully update the groups field
    Given a "default" catalog configuration
    And the following products:
      | sku             |
      | pack1           |
      | pack2           |
      | pack1_and_pack2 |
    Given the following group type:
      | code |
      | PACK |
    And the following product groups:
      | code  | label-en_US | type |
      | PACK1 | First pack  | PACK |
      | PACK2 | Second pack | PACK |
      | PACK3 | Third pack  | PACK |
    Then I should get the following products after apply the following updater to it:
      | product         | actions                                                                                                                           | result                         |
      | pack1           | [{"type": "set_data", "field": "groups", "data": ["PACK1"]}]                                                                      | {"groups": ["PACK1"]}          |
      | pack1           | [{"type": "set_data", "field": "groups", "data": []}]                                                                             | {"groups": []}                 |
      | pack2           | [{"type": "set_data", "field": "groups", "data": ["PACK2"]}]                                                                      | {"groups": ["PACK2"]}          |
      | pack1_and_pack2 | [{"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2"]}]                                                             | {"groups": ["PACK1", "PACK2"]} |
      | pack2           | [{"type": "set_data", "field": "groups", "data": ["PACK1", "PACK2"]}, {"type": "set_data", "field": "groups", "data": ["PACK3"]}] | {"groups": ["PACK3"]}          |
