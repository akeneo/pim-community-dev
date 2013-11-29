Feature: Switch product locale
  In order to manage product information in different languages
  as a PIM User
  I need to be able to switch product locale

  Background:
    Given an "apparel" catalog configuration
    And the following product:
      | sku    | family  |
      | jacket | jackets |
    And the following product values:
      | product | attribute | value     | locale |
      | jacket  | name      | My jacket | en_US  |
      | jacket  | name      | Ma veste  | fr_FR  |
    And I am logged in as "admin"
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

  Scenario: Successfully edit a product in another locale
    Given I switch the locale to "German"
    Then the product Name should be empty
    When I change the Name to "Meine Jacke"
    And I save the product
    Then the product Name should be "Meine Jacke"
