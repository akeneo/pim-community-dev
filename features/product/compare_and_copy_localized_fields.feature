Feature: Compare and copy localized fields
  In order to reuse enrich values in other languages
  As Julia
  I need to be able to compare and copy values in different languages

  Background:
    Given a "apparel" catalog configuration
    And the following product:
      | sku    | family  |
      | tshirt | tshirts |
    And the following product values:
      | product | attribute   | value               | locale | scope     |
      | tshirt  | description | City shoes          | en_US  | ecommerce |
      | tshirt  | description | Chaussures de ville | fr_FR  | ecommerce |
      | tshirt  | name        | Floup               | fr_FR  |           |
    And I am logged in as "Julia"

  Scenario: Successfully display available comparison languages
    Given I am on the "tshirt" product page
    Then I should see comparison languages "German (Germany), English (United Kingdom) and French (France)"

  @javascript
  Scenario: Successfully copy all compared product localized values
    Given I am on the "tshirt" product page
    When I compare values with the "French (France)" translation
    And I copy all translations
    Then the product Name should be "Floup"
    And the product ecommerce Description should be "Chaussures de ville"
