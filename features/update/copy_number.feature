Feature: Update number fields
  In order to update products
  As an internal process or any user
  I need to be able to update a copy number field of a product

  Scenario: Successfully update a number field
    Given a "apparel" catalog configuration
    And the following attributes:
      | code       | type               | group | decimals_allowed | negative_allowed |
      | item_count | pim_catalog_number | other | 0                | 0                |
      | car_count  | pim_catalog_number | other | 0                | 0                |
    And the following products:
      | sku | item_count |
      | AKN | 123        |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                      | result                                     |
      | AKN     | [{"type": "copy_data", "from_field": "item_count", "to_field": "car_count"}] | {"values": {"car_count": [{"data": 123}]}} |
