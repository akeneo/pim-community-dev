Feature: Update metric fields
  In order to update products
  As an internal process or any user
  I need to be able to update a copy metric field of a product

  Scenario: Successfully update a metric field
    Given a "default" catalog configuration
    And the following attributes:
      | code   | type   | metricFamily | defaultMetricUnit |
      | width  | metric | Length       | METER             |
      | height | metric | Length       | METER             |
    And the following products:
      | sku  | width         |
      | BOX1 | 30 CENTIMETER |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                              | result                                                                   |
      | BOX1    | [{"type": "copy_data", "from_field": "width", "to_field": "height"}] | {"values": {"height": [{"data": {"amount": 30, "unit": "CENTIMETER"}}]}} |
