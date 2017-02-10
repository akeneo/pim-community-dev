Feature: Update family fields
  In order to update products
  As an internal process or any user
  I need to be able to update the family field of a product

  Scenario: Successfully update the family field
    Given a "default" catalog configuration
    And the following products:
      | sku     |
      | tshirt1 |
      | tshirt2 |
    And the following families:
      | code  | requirements-ecommerce | requirements-mobile |
      | shirt | sku                    | sku                 |
      | mug   | sku                    | sku                 |
      | tv    | sku                    | sku                 |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                           | result              |
      | tshirt1 | [{"type": "set_data", "field": "family", "data": "shirt"}]                                                        | {"family": "shirt"} |
      | tshirt1 | [{"type": "set_data", "field": "family", "data": null}]                                                           | {"family": null}    |
      | tshirt2 | [{"type": "set_data", "field": "family", "data": "mug"}]                                                          | {"family": "mug"}   |
      | tshirt2 | [{"type": "set_data", "field": "family", "data": "shirt"}, {"type": "set_data", "field": "family", "data": "tv"}] | {"family": "tv"}    |
