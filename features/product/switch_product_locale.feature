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
    And I am on the "jacket" product page

  Scenario: Succesfully display and edit a product in the default locale
    Then the locale switcher should contain the following items:
      | language                 | label     |
      | English (United States)  | My jacket |
      | English (United Kingdom) | jacket    |
      | German (Germany)         | jacket    |
      | French (France)          | Ma veste  |
    And the product Name should be "My jacket"
    When I change the Name to "My cool jacket"
    And I save the product
    Then the product Name should be "My cool jacket"

  @javascript
  Scenario: Successfully edit a product in another locale
    Given I switch the locale to "German (Germany)"
    Then the product Name should be empty
    When I change the Name to "Meine Jacke"
    And I save the product
    Then the product Name should be "Meine Jacke"
