@javascript
Feature: Show localized rules
  In order have localized UI
  As a regular user
  I need to see localized attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code                   | label-en_US            | type                         | decimals_allowed | negative_allowed | metric_family | default_metric_unit | group |
      | decimal_number         | decimal_number         | pim_catalog_number           | 1                | 0                |               |                     | other |
      | another_decimal_number | another_decimal_number | pim_catalog_number           | 1                | 0                |               |                     | other |
      | decimal_price          | decimal_price          | pim_catalog_price_collection | 1                |                  |               |                     | other |
      | decimal_metric         | decimal_metric         | pim_catalog_metric           | 1                | 0                | Length        | CENTIMETER          | other |
    And the following product rule definitions:
      """
      my_rule:
        priority: 10
        conditions:
          - field:    decimal_number
            operator: =
            value:    10.5
        actions:
          - type:   set
            field:  another_decimal_number
            value:  5.56789
          - type:   set
            field:  decimal_price
            value:
              - amount:     12.5
                currency: EUR
          - type:   set
            field:  decimal_metric
            value:
              amount: 10.5
              unit: CENTIMETER
      """
