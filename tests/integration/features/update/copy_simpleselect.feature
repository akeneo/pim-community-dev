Feature: Update simple select fields
  In order to update products
  As an internal process or any user
  I need to be able to copy a simple select field of a product

  Scenario: Successfully update a simple select field
    Given a "default" catalog configuration
    And the following attributes:
      | code        | type                     | group |
      | front_color | pim_catalog_simpleselect | other |
      | back_color  | pim_catalog_simpleselect | other |
    And the following "front_color" attribute options: Red and Yellow
    And the following "back_color" attribute options: Red and Yellow
    And the following products:
      | sku                 | front_color |
      | MONOCHROMATIC_PAPER | Red         |
    Then I should get the following products after apply the following updater to it:
      | product             | actions                                                                        | result                                        |
      | MONOCHROMATIC_PAPER | [{"type": "copy_data", "from_field": "front_color", "to_field": "back_color"}] | {"values": {"back_color": [{"data": "Red"}]}} |
