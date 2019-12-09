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
