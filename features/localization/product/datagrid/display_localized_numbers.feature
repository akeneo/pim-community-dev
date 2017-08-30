@javascript
Feature: Localize numbers in the product grid
  In order to have localized UI
  As a product manager
  I need to be able to show localized numbers in the product grid

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code      | label-en_US | type                         | decimals_allowed | label-fr_FR | useable_as_grid_filter | group |
      | big_price | Big price   | pim_catalog_price_collection | 1                | Gros prix   | 1                      | other |
    And the following products:
      | sku     | big_price                | rate_sale | weight             |
      | sandals | 1000.12 USD, 1000.01 EUR | 1000.1234 | 1000.3456 KILOGRAM |

  Scenario: Successfully show English format numbers for English UI
    Given I am logged in as "Julia"
    When I am on the products grid
    And I display the columns SKU, Big price, Rate of sale and Weight
    Then the row "sandals" should contain:
      | column       | value                |
      | Big price    | $1,000.12, €1,000.01 |
      | Rate of sale | 1,000.1234           |
      | Weight       | 1,000.3456 Kilogram  |

  # https://akeneo.atlassian.net/browse/PIM-6020
  @skip
  Scenario: Successfully show French format numbers for French UI
    Given I am logged in as "Julien"
    When I am on the products grid
    And I display the columns SKU, Big price, Rate of sale and Weight
    Then the row "sandals" should contain:
      | column       | value                    |
      | Big price    | 1 000,12 $US, 1 000,01 € |
      | Rate of sale | 1 000,1234               |
      | Weight       | 1 000,3456 Kilogramme    |

  @skip
  Scenario: Successfully show English format numbers for French catalog
    Given I am logged in as "Julia"
    And I add the "french" locale to the "mobile" channel
    And I am on the products grid
    And I switch the locale to "fr_FR"
    When I display the columns [sku], Gros prix, Taux de vente and Poids
    Then the row "sandals" should contain:
      | column        | value                |
      | Gros prix     | $1,000.12, €1,000.01 |
      | Taux de vente | 1,000.1234           |
      | Poids         | 1,000.3456 Kilogram  |
