@javascript
Feature: Define product prices
  In order to define a product prices
  As a regular user
  I need to be able to define prices in different currencies for each locale

  Background:
    Given the "default" catalog configuration
    And the following attribute:
      | code        | label-en_US  | type                         | localizable | group | decimals_allowed |
      | publicPrice | Public price | pim_catalog_price_collection | 1           | other | 0                |
    And the following product:
      | sku  | publicPrice-en_US | publicPrice-fr_FR |
      | bike | 50 EUR, 100 USD   | 150 EUR, 200 USD  |
    And I am logged in as "Mary"

  @critical
  Scenario: Successfully edit prices in french locale
    Given I am on the "bike" product page
    Then the product Public price in USD should be "100.00"
    And the product Public price in EUR should be "50.00"
    Given I switch the locale to "fr_FR"
    When I change the "[publicPrice]" to "700.00 USD"
    And I save the product
    Then the product [publicPrice] in USD should be "700.00"
    And the product [publicPrice] in EUR should be "150.00"
