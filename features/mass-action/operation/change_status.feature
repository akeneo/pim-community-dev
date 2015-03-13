Feature: Run mass edit actions to change status of many products at once
  In order to include or exclude many products in or from the export
  I need to be able to change the status of many products

  Scenario: Successfully mass-edit products to enable them
    Given the "apparel" catalog configuration
    And the following products:
      | sku     |
      | BOOTBXS |
      | BOOTWXS |
      | BOOTBS  |
      | BOOTBL  |
      | MUGRXS  |
    Then I should get the following products after apply the following mass-edit operation to it:
      | operation     | filters                                                            | actions                                | result             |
      | change-status | [{"field":"sku", "operator":"IN", "value": ["BOOTBL", "MUGRXS"]}]  | [{"field": "enabled", "value": true}]  | {"enabled": true}  |
      | change-status | [{"field":"sku", "operator":"=", "value": "BOOTBS"}]               | [{"field": "enabled", "value": true}]  | {"enabled": true}  |
      | change-status | [{"field":"sku", "operator":"IN", "value": ["BOOTWXS", "MUGRXS"]}] | [{"field": "enabled", "value": false}] | {"enabled": false} |
      | change-status | [{"field":"enabled", "operator":"=", "value": false}]              | [{"field": "enabled", "value": true}]  | {"enabled": true}  |
      | change-status | [{"field":"enabled", "operator":"=", "value": true}]               | [{"field": "enabled", "value": false}] | {"enabled": false} |
