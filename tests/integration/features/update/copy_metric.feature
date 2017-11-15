Feature: Update metric fields
  In order to update products
  As an internal process or any user
  I need to be able to update a copy metric field of a product

  Scenario: Successfully update a metric field
    Given a "default" catalog configuration
    And the following attributes:
      | code   | type               | metric_family | default_metric_unit | group | decimals_allowed | negative_allowed |
      | width  | pim_catalog_metric | Length        | METER               | other | 0                | 0                |
      | height | pim_catalog_metric | Length        | METER               | other | 0                | 0                |
    And the following products:
      | sku  | width         |
      | BOX1 | 30 CENTIMETER |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                              | result                                                                   |
      | BOX1    | [{"type": "copy_data", "from_field": "width", "to_field": "height"}] | {"values": {"height": [{"data": {"amount": 30, "unit": "CENTIMETER"}}]}} |
