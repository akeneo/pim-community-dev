@javascript
Feature: Show localized attributes in compare mode
  In order have localized UI
  As a regular user
  I need to see localized attribute values

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code           | label          | type   | decimals_allowed | metric_family | default metric unit | localizable |
      | decimal_price  | decimal_price  | prices | yes              |               |                     | yes         |
      | decimal_number | decimal_number | number | yes              |               |                     | yes         |
      | decimal_metric | decimal_metric | metric | yes              | Length        | CENTIMETER          | yes         |
    And the following products:
      | sku     | decimal_number-en_US | decimal_price-fr_FR  | decimal_number-fr_FR | decimal_metric-fr_FR |
      | sandals | 1                    | 10.12 USD, 10.12 EUR | 12.1234              | 10.3456 CENTIMETER   |

  Scenario:
    Given I am logged in as "Mary"
    And I edit the "sandals" product
    When I compare values with the "fr_FR" translation
    Then the compared field decimal_price should contain "10.12"
    And the compared field decimal_number should contain "12.1234"
    And the compared field decimal_metric should contain "10.3456"

  Scenario:
    Given I am logged in as "Julien"
    And I edit the "sandals" product
    When I compare values with the "fr_FR" translation
    Then the compared field decimal_price should contain "10,12"
    And the compared field decimal_number should contain "12,1234"
    And the compared field decimal_metric should contain "10,3456"
