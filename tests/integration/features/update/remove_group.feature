Feature: Remove groups fields
  In order to update products
  As an internal process or any user
  I need to be able to remove groups field of a product

  Scenario: Successfully update the groups field
    Given a "default" catalog configuration
    And the following group type:
      | code |
      | PACK |
    And the following product groups:
      | code  | label-en_US | type |
      | PACK1 | First pack  | PACK |
      | PACK2 | Second pack | PACK |
      | PACK3 | Third pack  | PACK |
    And the following products:
      | sku               | groups              |
      | pack1             | PACK1               |
      | pack2             | PACK2               |
      | pack1_pack2       | PACK1, PACK2        |
      | pack2_pack3       | PACK2, PACK3        |
      | pack1_pack2_pack3 | PACK1, PACK2, PACK3 |
    Then I should get the following products after apply the following updater to it:
      | product           | actions                                                                                                                                 | result                |
      | pack1             | [{"type": "remove_data", "field": "groups", "data": ["PACK1"]}]                                                                         | {"groups": []}        |
      | pack2             | [{"type": "remove_data", "field": "groups", "data": []}]                                                                                | {"groups": ["PACK2"]} |
      | pack1_pack2       | [{"type": "remove_data", "field": "groups", "data": ["PACK2"]}]                                                                         | {"groups": ["PACK1"]} |
      | pack2_pack3       | [{"type": "remove_data", "field": "groups", "data": ["PACK2", "PACK3"]}]                                                                | {"groups": []}        |
      | pack1_pack2_pack3 | [{"type": "remove_data", "field": "groups", "data": ["PACK1", "PACK2"]}, {"type": "remove_data", "field": "groups", "data": ["PACK3"]}] | {"groups": []}        |
