@javascript
Feature: Localize numbers in the published product grid
  In order to have localized UI
  As a product manager
  I need to be able to show localized numbers in the published product grid

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code      | label     | label-fr_FR | type   | decimals_allowed | useable_as_grid_filter |
      | big_price | big_price | big_price   | prices | yes              | yes                    |
    And the following published products:
      | sku     | big_price                | rate_sale    | weight             |
      | sandals | 1000.12 USD, 1000.01 EUR | 1000.1234    | 1000.3456 KILOGRAM |

  Scenario: Successfully show English format numbers for English UI
    Given I am logged in as "Julia"
    And I am on the published index page
    When I display in the published products grid the columns sku, big_price, rate_sale, weight
    Then the row "sandals" should contain:
      | column       | value                |
      | big_price    | $1,000.12, €1,000.01 |
      | Rate of Sale | 1,000.1234           |
      | weight       | 1,000.3456 Kilogram  |

  Scenario: Successfully show French format numbers for French UI
    Given I am logged in as "Julien"
    When I am on the published index page
    And I display in the published products grid the columns sku, big_price, rate_sale, weight
    Then the row "sandals" should contain:
      | column       | value                    |
      | big_price    | 1 000,12 $US, 1 000,01 € |
      | Rate of Sale | 1 000,1234               |
      | weight       | 1 000,3456 Kilogramme    |

  Scenario: Successfully show English format numbers for French catalog
    Given I am logged in as "Julia"
    And I add the "french" locale to the "mobile" channel
    And I am on the published index page
    And I display in the published products grid the columns sku, big_price, rate_sale, weight
    When I switch the locale to "French (France)"
    Then the row "sandals" should contain:
      | column        | value                |
      | big_price     | $1,000.12, €1,000.01 |
      | Taux de vente | 1,000.1234           |
      | Poids         | 1,000.3456 Kilogram  |
