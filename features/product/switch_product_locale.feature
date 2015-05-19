@javascript
Feature: Switch product locale
  In order to manage product information in different languages
  as Mary
  I need to be able to switch product locale

  Background:
    Given an "apparel" catalog configuration
    And the following product:
      | sku    | family  | name-en_US | name-fr_FR |
      | jacket | jackets | My jacket  | Ma veste   |
    And I am logged in as "Mary"
    And I edit the "jacket" product

  @cucumberjs
  Scenario: Successfully display and edit a product in the default locale
    Then the locale switcher should contain the following items:
      | language | flag    | locale |
      | English  | flag-us | en_US  |
      | English  | flag-gb | en_GB  |
      | German   | flag-de | de_DE  |
      | French   | flag-fr | fr_FR  |
    And the product "name" should be "My jacket"
    When I change the "name" to "My cool jacket"
    And I save the product
    Then the product "name" should be "My cool jacket"

  Scenario: Successfully edit a product in another locale
    Given I switch the locale to "de_DE"
    Then the product Name should be empty
    When I change the Name to "Meine Jacke"
    And I save the product
    Then the product Name should be "Meine Jacke"
