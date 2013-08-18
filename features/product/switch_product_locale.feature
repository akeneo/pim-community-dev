Feature: Switch product locale
  In order to manage product information in different languages
  as a PIM User
  I need to be able to switch product locale

  Background:
    Given a "Computer" product
    And the "Computer" product has the following translations:
      | locale  | attribute | value      |
      | english | name      | computer   |
      | french  | name      | ordinateur |
      | english | screen    | 15 inches  |
    And I am logged in as "admin"

  Scenario: Succesfully display product in the current locale
    Given I am on the "Computer" product page
    Then the product name should be "computer"

  Scenario: Successfully switch product current locale
    Given I am on the "Computer" product page
    When I switch the locale to "French"
    Then the product name should be "ordinateur"

  Scenario: Successfully display nothing if no translation was set
    Given I am on the "Computer" product page
    When I switch the locale to "French"
    Then the product screen should be empty

  Scenario: Successfully edit a translated value in the default locale
    Given I am on the "Computer" product page
    When I change the name to "laptop"
    And I save the product
    Then the product name should be "laptop"

  Scenario: Sucessfully edit a translated value in another locale
    Given I am on the "Computer" product page
    And I switch the locale to "French"
    When I change the name to "ordinateur portable"
    And I save the product
    Then the product name should be "ordinateur portable"

  Scenario: Succesfully display translated product label in the locale switcher
    Given the following family:
      | code       |
      | technology |
    And the family "technology" has the following attribute:
      | label | attribute as label |
      | name  | yes                |
    And the product "Computer" belongs to the family "Technology"
    And I am on the "Computer" product page
    Then the locale switcher should contain the following items:
      | language                | label      |
      | English (United States) | computer   |
      | French                  | ordinateur |
