@javascript
Feature: Display the localized product history for complex prices
  In order to have complete localized UI
  As a product manager
  I need to have show localized localizable and scopable prices

  Background:
    Given a "apparel" catalog configuration
    And the following attributes:
      | code            | label-en_US     | label-fr_FR     | type                         | decimals_allowed | group | available_locales | localizable | scopable |
      | localized_price | localized_price | localized_price | pim_catalog_price_collection | 1                | other | fr_FR,en_US       | 1           | 0        |
      | scoped_price    | scoped_price    | scoped_price    | pim_catalog_price_collection | 1                | other |                   | 0           | 1        |
      | complex_price   | complex_price   | complex_price   | pim_catalog_price_collection | 1                | other | fr_FR,en_US       | 1           | 1        |
    And I am logged in as "admin"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | sandal |
    And I press the "Save" button in the popin
    And I should be on the product "sandal" edit page
    And I edit the "sandal" product
    And I add available attributes localized_price, scoped_price and complex_price
    And I change the "localized_price" to "0.12 EUR"
    And I change the "localized_price" to "3.45 USD"
    And I change the "scoped_price" to "2.34 EUR"
    And I change the "scoped_price" to "5.67 USD"
    And I change the "complex_price" to "4.56 EUR"
    And I change the "complex_price" to "7.89 USD"
    And I switch the locale to "fr_FR"
    And I change the "localized_price" to "1.23 EUR"
    And I change the "localized_price" to "4.56 USD"
    And I change the "complex_price" to "5.67 EUR"
    And I change the "complex_price" to "8.90 USD"
    And I switch the scope to "print"
    And I change the "scoped_price" to "3.45 EUR"
    And I change the "scoped_price" to "6.78 USD"
    And I switch the locale to "en_US"
    And I change the "complex_price" to "5.67 EUR"
    And I change the "complex_price" to "8.90 USD"
    And I save the product
    And I logout
    And the history of the product "sandal" has been built

  Scenario: Display french-format product history prices
    Given I am logged in as "Julien"
    And I edit the "sandal" product
    When I visit the "Historique" column tab
    Then there should be 2 update
    And I should see history:
      | version | property                       | value    |
      | 2       | localized_price EUR en         | 0,12 €   |
      | 2       | localized_price USD en         | 3,45 $US |
      | 2       | localized_price EUR fr         | 1,23 €   |
      | 2       | localized_price USD fr         | 4,56 $US |
      | 2       | scoped_price EUR ecommerce     | 2,34 €   |
      | 2       | scoped_price USD ecommerce     | 5,67 $US |
      | 2       | scoped_price EUR print         | 3,45 €   |
      | 2       | scoped_price USD print         | 6,78 $US |
      | 2       | complex_price EUR ecommerce en | 4,56 €   |
      | 2       | complex_price USD ecommerce en | 7,89 $US |
      | 2       | complex_price EUR print en     | 5,67 €   |
      | 2       | complex_price USD print en     | 8,90 $US |
      | 2       | complex_price EUR ecommerce fr | 5,67 €   |
      | 2       | complex_price USD ecommerce fr | 8,90 $US |
