@javascript
Feature: Compare and copy localized fields
  In order to reuse enrich values in other languages
  As a regular user
  I need to be able to compare and copy values in different languages

  Background:
    Given a "apparel" catalog configuration
    And the following product:
      | sku    | family  | name-fr_FR | legend-en_US | legend-fr_FR | description-en_US-ecommerce | description-fr_FR-ecommerce |
      | tshirt | tshirts | Floup      | Front view   | Vue de face  | City shoes                  | Chaussures de ville         |
    And I am logged in as "Mary"

  Scenario: Successfully display available comparison languages
    Given I am on the "tshirt" product page
    Then I should see comparison languages "German (Germany), English (United Kingdom) and French (France)"

  Scenario: Successfully copy all compared product localized values
    Given I am on the "tshirt" product page
    When I compare values with the "French (France)" translation
    And I select all translations
    And I copy selected translations
    Then the product Name should be "Floup"
    And the product ecommerce Description should be "Chaussures de ville"
    And the product Legend should be "Vue de face"

  Scenario: Successfully copy current tab compared product localized values
    Given I am on the "tshirt" product page
    When I compare values with the "French (France)" translation
    And I select current tab translations
    And I copy selected translations
    Then the product Name should be "Floup"
    And the product ecommerce Description should be "Chaussures de ville"
    And the product Legend should be "Front view"

  Scenario: Successfully copy manually selected compared product localized values
    Given I am on the "tshirt" product page
    When I compare values with the "French (France)" translation
    And I select translations for "Name"
    And I copy selected translations
    Then the product Name should be "Floup"
    And the product ecommerce Description should be "City shoes"
    And the product Legend should be "Front view"
