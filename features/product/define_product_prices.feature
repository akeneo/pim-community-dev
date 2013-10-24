Feature: Define product prices
  In order to define a product prices
  As Julia
  I need to be able to define prices in different currencies for for each locale

  Background:
    Given the following currencies:
      | code | activated |
      | USD  | yes       |
      | EUR  | yes       |
    Given the following channels:
      | code      | currencies |
      | ecommerce | EUR, USD   |
      | mobile    | EUR, USD   |
    Given a "bike" product
    And the following product attributes:
      | product | label        | type   | translatable |
      | bike    | Public price | prices | yes          |
    And the following product values:
      | product | attribute   | locale | value            |
      | bike    | publicPrice | en_US  | 50 EUR, 100 USD  |
      | bike    | publicPrice | fr_FR  | 150 EUR, 200 USD |
    And I am logged in as "Julia"

  Scenario: Successfully display english public prices
    Given I am on the "bike" product page
    Then the product Public price in $ should be "100.00"
    And the product Public price in € should be "50.00"

  Scenario: Successfully display french public prices
    Given I am on the "bike" product page
    And I switch the locale to "French"
    Then the product [publicPrice] in $ should be "200.00"
    And the product [publicPrice] in € should be "150.00"

  Scenario: Successfully update the french public prices
    Given I am on the "bike" product page
    And I switch the locale to "French"
    When I change the "$ [publicPrice]" to "700.00"
    And I save the product
    Then the product [publicPrice] in $ should be "700.00"
    And the product [publicPrice] in € should be "150.00"
