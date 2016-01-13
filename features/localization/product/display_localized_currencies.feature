@javascript
Feature: Display localized currencies in the product grid
  In order to have a complete localized UI
  As a regular user
  I need to be able to view localized currencies

  Background:
    Given the "apparel" catalog configuration
    And the following published products:
      | sku    | price               |
      | sandal | 12.50 USD,15.50 EUR |

  Scenario: Successfully show english currencies
    Given I am logged in as "Julia"
    And I am on the published index page
    When I display in the published products grid the columns price
    And I should see "€15.50"
    And I should see "$12.50"

  Scenario: Successfully show french currencies
    Given I am logged in as "Julien"
    And I am on the published index page
    When I display in the published products grid the columns price
    And I should see "15,50 €"
    And I should see "12,50 $US"
