Feature: Update multi select fields
  In order to update products
  As an internal process or any user
  I need to be able to copy a multi select field of a product

  Scenario: Successfully update a multi select field
    Given a "default" catalog configuration
    And the following attributes:
      | code          | type                    | group |
      | body_color    | pim_catalog_multiselect | other |
      | sleeves_color | pim_catalog_multiselect | other |
    And the following "body_color" attribute options: Red, Yellow, Black and White
    And the following "sleeves_color" attribute options: Red, Yellow, Black and White
    And the following products:
      | sku             | body_color   |
      | STRIPED_T_SHIRT | Black, White |
    Then I should get the following products after apply the following updater to it:
      | product         | actions                                                                          | result                                                                |
      | STRIPED_T_SHIRT | [{"type": "copy_data", "from_field": "body_color", "to_field": "sleeves_color"}] | {"values": {"sleeves_color": [{"data": {"0":"Black", "1":"White"}}]}} |
