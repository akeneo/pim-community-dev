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
    And I start the copy
    Then the copy locale switcher should contain the following items:
      | language | flag    | locale |
      | German   | flag-de | de_DE  |
      | English  | flag-gb | en_GB  |
      | English  | flag-us | en_US  |
      | French   | flag-fr | fr_FR  |

  Scenario: Successfully copy all compared product localized values
    Given I am on the "tshirt" product page
    When I compare values with the "fr_FR" translation
    And I select all translations
    And I copy selected translations
    Then the product Name should be "Floup"
    And the product Description for scope "ecommerce" should be "Chaussures de ville"
    And I visit the "Media" group
    And the product Legend should be "Vue de face"

  Scenario: Successfully copy current tab compared product localized values
    Given I am on the "tshirt" product page
    When I compare values with the "fr_FR" translation
    And I select all visible translations
    And I copy selected translations
    Then the product Name should be "Floup"
    And the product Description for scope "ecommerce" should be "Chaussures de ville"
    And I visit the "Media" group
    And the product Legend should be "Front view"

  Scenario: Successfully copy manually selected compared product localized values
    Given I am on the "tshirt" product page
    When I compare values with the "fr_FR" translation
    And I select translations for "Name"
    And I copy selected translations
    Then the product Name should be "Floup"
    And the product Description for scope "ecommerce" should be "City shoes"
    And I visit the "Media" group
    And the product Legend should be "Front view"
