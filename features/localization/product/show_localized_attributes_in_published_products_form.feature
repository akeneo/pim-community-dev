@javascript
Feature: Show localized attributes in published products form
  In order have localized UI
  As a regular user
  I need to see localized attribute values

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code           | label          | type   | decimals_allowed | metric_family | default metric unit |
      | decimal_price  | decimal_price  | prices | yes              |               |                     |
      | decimal_number | decimal_number | number | yes              |               |                     |
      | decimal_metric | decimal_metric | metric | yes              | Length        | CENTIMETER          |
    And the following published products:
      | sku     | decimal_price        | decimal_number | decimal_metric     |
      | sandals | 10.12 USD, 10.12 EUR | 12.1234        | 10.3456 CENTIMETER |

  Scenario: Successfully show English format numbers for English UI
    Given I am logged in as "Julia"
    When I show the "sandals" published
    Then the field decimal_price should contain "10.12"
    And the field decimal_number should contain "12.1234"
    And the field decimal_metric should contain "10.3456"

  Scenario: Successfully show French format numbers for French UI
    Given I am logged in as "Julien"
    When I show the "sandals" published
    Then the field decimal_price should contain "10,12"
    And the field decimal_number should contain "12,1234"
    And the field decimal_metric should contain "10,3456"
