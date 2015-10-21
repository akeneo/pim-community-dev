@javascript
Feature: Localize numbers in the datagrid
  In order to have localized UI
  As a product manager
  I need to be able to show localized numbers in the published product grid

  Background:
    Given a "footwear" catalog configuration
    And the following published products:
      | sku     | price    | rate_sale | weight           |
      | sandals | 5.12 USD | 0.1234    | 12.3456 KILOGRAM |
    And I am logged in as "Julia"

  Scenario: Successfully show English format numbers for English UI
    Given I am on the published index page
    When I display the published products columns sku, price, rate_sale, weight
    Then the row "sandals" should contain:
      | column       | value            |
      | price        | 5.12 $           |
      | Rate of Sale | 0.1234           |
      | weight       | 12.3456 Kilogram |

  Scenario: Successfully show French format numbers for French UI
    Given I edit my profile
    And I visit the "Interfaces" tab
    And I fill in the following information:
      | Ui locale | French (France) |
    And I save the user
    When I am on the published index page
    And I display the published products columns sku, price, rate_sale, weight
    Then the row "sandals" should contain:
      | column       | value            |
      | price        | 5,12 $US         |
      | Rate of Sale | 0,1234           |
      | weight       | 12,3456 KILOGRAM |

  Scenario: Successfully show English format numbers for French catalog
    Given I add the "french" locale to the "mobile" channel
    And I am on the published index page
    When I switch the locale to "French (France)"
    And I display the published products columns sku, price, rate_sale, weight
    Then the row "sandals" should contain:
      | column       | value            |
      | price        | 5.12 $           |
      | Rate of Sale | 0.1234           |
      | weight       | 12.3456 Kilogram |
