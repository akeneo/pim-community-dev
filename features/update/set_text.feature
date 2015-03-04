Feature: Update simple select fields
  In order to update products
  As an internal process or any user
  I need to be able to update a simple select field of a product

  Scenario: Successfully update a simple select field
    Given a "apparel" catalog configuration
    And the following products:
      | sku      |
      | AKN_MUG  |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                  | result                                                                                   |
      | AKN_MUG | [{"type": "set_value", "field": "name", "value": "Akeneo mug", "locale": "en_US", "scope": null}]        | {"values": {"name": [{"locale": "en_US", "scope": null, "value": "Akeneo mug"}]}}        |
      | AKN_MUG | [{"type": "set_value", "field": "description", "value": "Mug Akeneo", "locale": "fr_FR", "scope": "ecommerce"}] | {"values": {"description": [{"locale": "fr_FR", "scope": "ecommerce", "value": "Mug Akeneo"}]}} |
