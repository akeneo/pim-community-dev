Feature: Update metric fields
  In order to update products
  As an internal process or any user
  I need to be able to update a metric field of a product

  Scenario: Successfully update a metric field
    Given a "default" catalog configuration
    And the following attributes:
      | code   | type               | localizable | scopable | metric_family | default_metric_unit | group | decimals_allowed | negative_allowed |
      | weight | pim_catalog_metric | 1           | 0        | Weight        | KILOGRAM            | other | 1                | 0                |
      | width  | pim_catalog_metric | 0           | 1        | Length        | METER               | other | 0                | 0                |
      | height | pim_catalog_metric | 1           | 1        | Length        | METER               | other | 1                | 0                |
      | depth  | pim_catalog_metric | 0           | 0        | Length        | METER               | other | 0                | 0                |
    And the following products:
      | sku  |
      | BOX1 |
      | BOX2 |
      | BOX3 |
      | BOX4 |
      | BOX5 |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                                                                                                  | result                                                                                                     |
      | BOX1    | [{"type": "set_data", "field": "weight", "data": {"amount": "12.4", "unit": "GRAM"}, "locale": "fr_FR", "scope": null}]                                                                                                                  | {"values": {"weight": [{"locale": "fr_FR", "scope": null, "data": {"amount": 12.4, "unit": "GRAM"}}]}}     |
      | BOX2    | [{"type": "set_data", "field": "width", "data": {"amount": 5, "unit": "METER"}, "locale": null, "scope": "mobile"}]                                                                                                                      | {"values": {"width": [{"locale": null, "scope": "mobile", "data": {"amount": 5, "unit": "METER"}}]}}       |
      | BOX3    | [{"type": "set_data", "field": "height", "data": {"amount": 5.3, "unit": "METER"}, "locale": "fr_FR", "scope": "mobile"}]                                                                                                                | {"values": {"height": [{"locale": "fr_FR", "scope": "mobile", "data": {"amount": 5.3, "unit": "METER"}}]}} |
      | BOX4    | [{"type": "set_data", "field": "depth", "data": {"amount": "5", "unit": "CENTIMETER"}, "locale": null, "scope": null}]                                                                                                                   | {"values": {"depth": [{"locale": null, "scope": null, "data": {"amount": 5, "unit": "CENTIMETER"}}]}}      |
      | BOX4    | [{"type": "set_data", "field": "depth", "data": {"amount": "5", "unit": "CENTIMETER"}, "locale": null, "scope": null}, {"type": "set_data", "field": "depth", "data": {"amount": "5", "unit": "METER"}, "locale": null, "scope": null}]  | {"values": {"depth": [{"locale": null, "scope": null, "data": {"amount": 5, "unit": "METER"}}]}}           |
      | BOX5    | [{"type": "set_data", "field": "depth", "data": {"amount": "5", "unit": "CENTIMETER"}, "locale": null, "scope": null}, {"type": "set_data", "field": "depth", "data": {"amount": null, "unit": "METER"}, "locale": null, "scope": null}] | {"values": {"depth": [{"locale": null, "scope": null, "data": {"amount": null, "unit": "METER"}}]}}        |
