
Feature: Enforce no permissions for a locale
  In order to be able to prevent some users from viewing product data in some locales
  As an administrator
  I need to be able to enforce no permissions for locales

  Background:
    Given an "apparel" catalog configuration
    And a "foo" product

  Scenario: Redirect users from the product page to the dashboard when they can't see product data in any locales
    Given the following locale accesses:
      | locale | user group | access |
      | en_US  | Manager    | none   |
      | en_GB  | Manager    | none   |
      | fr_FR  | Manager    | none   |
      | de_DE  | Manager    | none   |
    And I am logged in as "Julia"
    When I am on the products page
    Then I should be on the homepage
    And I should see "You don't have access to product data in any activated locale, please contact your administrator"

  @javascript
  Scenario: Display only available locales in the locale switcher
    Given the following locale accesses:
      | locale | user group | access |
      | en_GB  | Manager    | none   |
      | fr_FR  | Manager    | none   |
    And I am logged in as "Julia"
    When I am on the products page
    Then the grid locale switcher should contain the following items:
      | language  | locale | flag    |
      | en   | en_US  | flag-us |
      | de   | de_DE  | flag-de |
    When I edit the "foo" product
    Then the locale switcher should contain the following items:
      | language  | locale | flag    |
      | English   | en_US  | flag-us |
      | German    | de_DE  | flag-de |

  @javascript
  Scenario: Display product view or edit page depending on user's rights
    Given the following locale accesses:
      | locale | user group | access |
      | en_US  | Manager    | view   |
    And I am logged in as "Julia"
    When I edit the "foo" product
    Then I should not see the "Save working copy" button
    When I switch the locale to "de_DE"
    Then I should see the "Save working copy" button
