Feature: Update text fields
  In order to update products
  As an internal process or any user
  I need to be able to update a text field of a product

  Scenario: Successfully update a text field
    Given a "apparel" catalog configuration
    And the following products:
      | sku     |
      | AKN_MUG |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                       | result                                                                                         |
      | AKN_MUG | [{"type": "set_data", "field": "name", "data": "Akeneo mug", "locale": "en_US", "scope": null}]               | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Akeneo mug"}]}}               |
      | AKN_MUG | [{"type": "set_data", "field": "description", "data": "Mug Akeneo", "locale": "fr_FR", "scope": "ecommerce"}] | {"values": {"description": [{"locale": "fr_FR", "scope": "ecommerce", "data": "Mug Akeneo"}]}} |
