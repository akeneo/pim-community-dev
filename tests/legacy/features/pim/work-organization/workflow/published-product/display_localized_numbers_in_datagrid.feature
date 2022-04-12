@javascript @published-product-feature-enabled
Feature: Localize numbers in the published product grid
  In order to have localized UI
  As a product manager
  I need to be able to show localized numbers in the published product grid

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code      | label-en_US | label-fr_FR | type                         | decimals_allowed | useable_as_grid_filter | group |
      | big_price | Big price   | Gros prix   | pim_catalog_price_collection | 1                | 1                      | other |
    And the following published products:
      | sku     | big_price                | rate_sale | weight             |
      | sandals | 1000.12 USD, 1000.01 EUR | 1000.1234 | 1000.3456 KILOGRAM |

  Scenario: Successfully show English format numbers for English UI
    Given I am logged in as "Julia"
    And I am on the published products grid
    When I display in the published products grid the columns SKU, Big price, Rate of Sale, Weight
    Then the row "sandals" should contain:
      | column       | value                |
      | Big price    | $1,000.12, €1,000.01 |
      | Rate of Sale | 1,000.1234           |
      | Weight       | 1,000.3456 Kilogram  |
