Feature: Update identifier fields
  In order to update products
  As an internal process or any user
  I need to be able to update an identifier field of a product

  Scenario: Successfully update an identifier field
    Given a "apparel" catalog configuration
    And the following products:
      | sku     |
      | AKN_MUG |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                          | result                                                                                        |
      | AKN_MUG | [{"type": "set_data", "field": "sku", "data": "AKN_MUG_PURPLE"}] | {"product": "AKN_MUG_PURPLE", "values": {"sku": [{"scope": null, "data": "AKN_MUG_PURPLE"}]}} |
