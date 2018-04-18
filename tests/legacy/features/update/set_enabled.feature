Feature: Update enabled fields
  In order to update products
  As an internal process or any user
  I need to be able to update enabled field of a product

  Scenario: Successfully update enabled field
    Given a "default" catalog configuration
    And the following products:
      | sku      |
      | enabled  |
      | disabled |
      | reverted |
    Then I should get the following products after apply the following updater to it:
      | product  | actions                                                                                                           | result             |
      | enabled  | [{"type": "set_data", "field": "enabled", "data": true}]                                                          | {"enabled": true}  |
      | disabled | [{"type": "set_data", "field": "enabled", "data": false}]                                                         | {"enabled": false} |
      | reverted | [{"type": "set_data", "field": "enabled", "data": true}, {"type": "set_data", "field": "enabled", "data": false}] | {"enabled": false} |
