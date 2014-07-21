@javascript
Feature: Define product prices
  In order to define a product prices
  As a regular user
  I need to be able to define prices in different currencies for each locale

  Background:
    Given the "default" catalog configuration
    And the following attribute:
      | label        | type   | localizable |
      | Public price | prices | yes         |
    And the following product:
      | sku  | publicPrice-en_US | publicPrice-fr_FR |
      | bike | 50 EUR, 100 USD   | 150 EUR, 200 USD  |
    And I am logged in as "Mary"

  Scenario: Successfully display english and french public prices
    Given I am on the "bike" product page
    Then the product Public price in $ should be "100.00"
    And the product Public price in € should be "50.00"
    When I switch the locale to "French (France)"
    Then the product [publicPrice] in $US should be "200.00"
    And the product [publicPrice] in € should be "150.00"

  Scenario: Successfully update the french public prices
    Given I am on the "bike" product page
    And I switch the locale to "French (France)"
    When I change the "$US [publicPrice]" to "700.00"
    And I save the product
    Then the product [publicPrice] in $US should be "700.00"
    And the product [publicPrice] in € should be "150.00"
