@javascript
Feature: Show localized rules
  In order have localized UI
  As a regular user
  I need to see localized attribute values

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code                   | label                  | type   | decimals_allowed | metric_family | default metric unit |
      | decimal_number         | decimal_number         | number | yes              |               |                     |
      | another_decimal_number | another_decimal_number | number | yes              |               |                     |
      | decimal_price          | decimal_price          | prices | yes              |               |                     |
      | decimal_metric         | decimal_metric         | metric | yes              | Length        | CENTIMETER          |
    And the following product rule definitions:
      """
      my_rule:
        priority: 10
        conditions:
          - field:    decimal_number
            operator: =
            value:    10.5
        actions:
          - type:   set_value
            field:  another_decimal_number
            value:  5.56789
          - type:   set_value
            field:  decimal_price
            value:
              - data:     12.5
                currency: EUR
          - type:   set_value
            field:  decimal_metric
            value:
              data: 10.5
              unit: CENTIMETER
      """

  Scenario: Successfully show english rules of an attribute
    Given I am logged in as "Julia"
    And I am on the "another_decimal_number" attribute page
    When I visit the "Rules" tab
    Then I should see the following rule conditions:
      | rule    | field          | operator | value |
      | my_rule | decimal_number | =        | 10.50 |
    And I should see the following rule setter actions:
      | rule    | field                  | value            |
      | my_rule | another_decimal_number | 5.5679           |
      | my_rule | decimal_price          | €12.50           |
      | my_rule | decimal_metric         | 10.50 Centimeter |

  Scenario: Successfully show french rules of an attribute
    Given I am logged in as "Julien"
    And I am on the "another_decimal_number" attribute page
    When I visit the "Règles" tab
    Then I should see the following rule conditions:
      | rule    | field          | operator | value |
      | my_rule | decimal_number | =        | 10,50 |
    And I should see the following rule setter actions:
      | rule    | field                  | value            |
      | my_rule | another_decimal_number | 5,5679           |
      | my_rule | decimal_price          | 12,50 €          |
      | my_rule | decimal_metric         | 10,50 CENTIMETER |
