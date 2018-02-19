Feature: Switching context on grid
  Scenario: When I switch the channel, the locale list is updated
    Given the following channels with locales:
      | code      | locales     |
      | ecommerce | en_US,fr_FR |
      | mobile    | fr_FR,de_DE |
    And the following product labels:
      | identifier | en_US           | fr_FR            | de_DE            |
      | shirt      | My nice product | Un produit sympa | Ein shon produkt |
    And a product grid is displayed
    Then the channel should be "ecommerce"
    Then the locale should be "en_US"
    Then the locale list should be "en_US,fr_FR"
    And I switch the channel to "mobile"
    Then the locale should be "fr_FR"
    Then the locale list should be "fr_FR,de_DE"
    And I switch the channel to "ecommerce"
    Then the locale should be "fr_FR"
    Then the locale list should be "en_US,fr_FR"

  Scenario: I can switch the locale and see updated results
    Given the following channels with locales:
      | code      | locales           |
      | ecommerce | en_US,de_DE,fr_FR |
    And the following product labels:
      | identifier | en_US           | fr_FR            | de_DE            |
      | shirt      | My nice product | Un produit sympa | Ein shon produkt |
    And a product grid is displayed
    Then the locale should be "en_US"
    And the product "label" of "shirt" should be "My nice product"
    And I switch the locale to "fr_FR"
    Then the locale should be "fr_FR"
    And the product "label" of "shirt" should be "Un produit sympa"
