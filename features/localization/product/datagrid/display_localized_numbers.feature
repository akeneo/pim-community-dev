@javascript
Feature: Localize numbers in the product grid
  In order to have localized UI
  As a product manager
  I need to be able to show localized numbers in the product grid

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code      | label     | type   | decimals_allowed | label-fr_FR | useable_as_grid_filter |
      | big_price | big_price | prices | yes              | big_price   | yes                    |
    And the following products:
      | sku     | big_price                | rate_sale    | weight             |
      | sandals | 1000.12 USD, 1000.01 EUR | 1000.1234    | 1000.3456 KILOGRAM |

  Scenario: Successfully show English format numbers for English UI
    Given I am logged in as "Julia"
    When I am on the products page
    And I display the columns sku, big_price, rate_sale and weight
    Then the row "sandals" should contain:
      | column       | value                |
      | big_price    | $1,000.12, €1,000.01 |
      | Rate of Sale | 1,000.1234           |
      | weight       | 1,000.3456 Kilogram  |

  Scenario: Successfully show French format numbers for French UI
    Given I am logged in as "Julien"
    When I am on the products page
    And I display the columns sku, big_price, rate_sale and weight
    Then the row "sandals" should contain:
      | column       | value                    |
      | big_price    | 1 000,12 $US, 1 000,01 € |
      | Rate of Sale | 1 000,1234               |
      | Poids        | 1 000,3456 Kilogramme    |

  Scenario: Successfully show English format numbers for French catalog
    Given I am logged in as "Julia"
    And I add the "french" locale to the "mobile" channel
    And I am on the products page
    And I display the columns sku, big_price, rate_sale and weight
    When I switch the locale to "fr_FR"
    Then the row "sandals" should contain:
      | column        | value                |
      | big_price     | $1,000.12, €1,000.01 |
      | Taux de vente | 1,000.1234           |
      | Poids         | 1,000.3456 Kilogram  |
