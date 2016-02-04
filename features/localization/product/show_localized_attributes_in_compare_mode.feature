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

  Scenario: Successfully show English format numbers for English UI
    Given I am logged in as "Mary"
    When I edit the "sandals" product
    And I open the comparison panel
    And I switch the comparison locale to "fr_FR"
    And I switch the comparison scope to "ecommerce"
    Then the decimal_price comparison value should be "10.12"
    And the decimal_number comparison value should be "12.1234"
    And the decimal_metric comparison value should be "10.3456 Centimeter"

  Scenario: Successfully show French format numbers for French UI
    Given I am logged in as "Julien"
    When I edit the "sandals" product
    And I open the comparison panel
    And I switch the comparison locale to "fr_FR"
    And I switch the comparison scope to "ecommerce"
    Then the decimal_price comparison value should be "10,12"
    And the decimal_number comparison value should be "12,1234"
    And the decimal_metric comparison value should be "10,3456 Centimètre"
