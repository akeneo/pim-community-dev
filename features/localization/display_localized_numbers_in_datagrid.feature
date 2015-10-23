@javascript
Feature: Localize numbers in the datagrid
  In order to have localized UI
  As a product manager
  I need to be able to show localized numbers in the published product grid

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code      | label     | type   | decimals_allowed |
      | big_price | big_price | prices | yes              |
    And the following published products:
      | sku     | big_price   | rate_sale    | weight             |
      | sandals | 1000.12 USD | 1000.1234    | 1000.3456 KILOGRAM |
    And I am logged in as "Julia"

  Scenario: Successfully show English format numbers for English UI
    Given I am on the published index page
    When I display in the published products grid the columns sku, big_price, rate_sale, weight
    Then the row "sandals" should contain:
      | column       | value               |
      | big_price    | 1,000.12 $          |
      | Rate of Sale | 1,000.1234          |
      | weight       | 1,000.3456 Kilogram |

  Scenario: Successfully show French format numbers for French UI
    Given I edit my profile
    And I visit the "Interfaces" tab
    And I fill in the following information:
      | Ui locale | French (France) |
    And I save the user
    When I am on the published index page
    And I display in the published products grid the columns sku, big_price, rate_sale, weight
    Then the row "sandals" should contain:
      | column       | value               |
      | big_price    | 1 000,12 $US        |
      | Rate of Sale | 1 000,1234          |
      | weight       | 1 000,3456 KILOGRAM |

  Scenario: Successfully show English format numbers for French catalog
    Given I add the "french" locale to the "mobile" channel
    And I am on the published index page
    When I switch the locale to "French (France)"
    And I display in the published products grid the columns sku, big_price, rate_sale, weight
    Then the row "sandals" should contain:
      | column       | value               |
      | big_price    | 1,000.12 $          |
      | Rate of Sale | 1,000.1234          |
      | weight       | 1,000.3456 Kilogram |
