@javascript
Feature: Localize dates in the product grid
  In order to have localized UI
  As a product manager
  I need to be able to show localized dates in the product grid

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | destocking_date |
      | sandals | 2015-01-31      |

  Scenario: Successfully show English format dates for English UI
    Given I am logged in as "Julia"
    When I am on the products page
    And I display the columns sku, destocking_date
    Then the row "sandals" should contain:
      | column          | value      |
      | Destocking date | 01/31/2015 |

  Scenario: Successfully show French format numbers for French UI
    Given I am logged in as "Julien"
    When I am on the products page
    And I display the columns sku, destocking_date
    Then the row "sandals" should contain:
      | column          | value      |
      | Destocking date | 31/01/2015 |

  Scenario: Successfully show English format numbers for French catalog
    Given I am logged in as "Julia"
    And I add the "french" locale to the "mobile" channel
    And I am on the products page
    And I display the columns sku, destocking_date
    When I switch the locale to "French (France)"
    Then the row "sandals" should contain:
      | column            | value      |
      | [Destocking_date] | 01/31/2015 |
