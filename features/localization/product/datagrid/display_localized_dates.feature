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
    When I am on the products grid
    And I display the columns SKU, Destocking date
    Then the row "sandals" should contain:
      | column          | value      |
      | Destocking date | 01/31/2015 |

  # https://akeneo.atlassian.net/browse/PIM-6020
  @skip
  Scenario: Successfully show French format dates for French UI
    Given I am logged in as "Julien"
    When I am on the products grid
    And I display the columns SKU, Destocking date
    Then the row "sandals" should contain:
      | column          | value      |
      | Destocking date | 31/01/2015 |

  @skip
  Scenario: Successfully show English format dates for French catalog
    Given I am logged in as "Julia"
    And I add the "french" locale to the "mobile" channel
    And I am on the products grid
    When I switch the locale to "fr_FR"
    And I display the columns [sku], Date de déstockage
    Then the row "sandals" should contain:
      | column             | value      |
      | Date de déstockage | 01/31/2015 |
