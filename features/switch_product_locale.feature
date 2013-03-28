Feature: Switch product locale
  In order to manage product information in different language
  as a PIM User
  I need to be able to switch product locale

  Background:
    Given a "Computer" product with the following translations:
      | locale  | attribute | value      |
      | english | name      | computer   |
      | french  | name      | ordinateur |
      | english | screen    | 15 inches  |
    And the current language is english
    And I am logged in as "admin"

  Scenario: Successfully display available product locales
    Given I am on the "Computer" product page
    Then I should see that the product is available in french and english

  Scenario: Succesfully display product in the current locale
    Given I am on the "Computer" product page
    Then the product name should be "computer"

  Scenario: Successfully switch product current locale
    Given I am on the "Computer" product page
    When I switch the locale to "french"
    Then the product name should be "ordinateur"

  Scenario: Successfully display nothing if no translation was set
    Given I am on the "Computer" product page
    When I switch the locale to "french"
    Then the product screen should be empty
