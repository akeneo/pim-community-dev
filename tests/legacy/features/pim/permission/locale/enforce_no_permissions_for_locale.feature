@javascript
Feature: Enforce no permissions for a locale
  In order to be able to prevent some users from viewing product data in some locales
  As an administrator
  I need to be able to enforce no permissions for locales

  Background:
    Given an "apparel" catalog configuration
    And a "foo" product

  @skip-nav
  Scenario: Redirect users from the product page to the dashboard when they can't see product data in any locales
    Given the following locale accesses:
      | locale | user group | access |
      | en_US  | Manager    | none   |
      | en_GB  | Manager    | none   |
      | fr_FR  | Manager    | none   |
      | de_DE  | Manager    | none   |
    And I am logged in as "Julia"
    When I am on the products grid
    Then I should be on the homepage
    And I should see the text "You don't have access to product data in any activated locale, please contact your administrator"

  Scenario: Display only available locales in the locale switcher
    Given the following locale accesses:
      | locale | user group | access |
      | en_GB  | Manager    | none   |
      | fr_FR  | Manager    | none   |
    And I am logged in as "Julia"
    When I am on the products grid
    When I edit the "foo" product
    Then the locale switcher should contain the following items:
      | language | locale | flag    |
      | English  | en_US  | flag-us |
      | German   | de_DE  | flag-de |

  Scenario: Display product view or edit page depending on user's rights
    Given the following product:
      | sku | name-en_US | name-en_GB |
      | bar | Name       | Name       |
    And the following locale accesses:
      | locale | user group | access |
      | en_US  | Manager    | edit   |
      | en_GB  | Manager    | view   |
    And I am logged in as "Julia"
    When I edit the "bar" product
    And I switch the locale to "en_GB"
    Then the field Name should be read only
    When I switch the locale to "en_US"
    And I change the "Name" to "My custom name"
    And I save the product
    Then the field Name should contain "My custom name"

  Scenario: Display only available locales in the product export builder
    Given the following locale accesses:
      | locale | user group | access |
      | en_GB  | Manager    | none   |
      | fr_FR  | Manager    | none   |
    And I am logged in as "Julia"
    And I am on the "ecommerce_product_export" export job edit page
    When I visit the "Content" tab
    Then I should see the text "German (Germany) English (United States)"
    And I should not see the text "French (France)"

  Scenario: display the next accessible locale
    Given the following product:
      | sku | name-en_US | name-en_GB |
      | bar | Name       | Name       |
    And the following locale accesses:
      | locale | user group | access |
      | en_US  | Manager    | edit   |
      | en_GB  | Manager    | view   |
    And I am logged in as "Julia"
    When I edit the "bar" product
    And I switch the locale to "en_GB"
    Then the field Name should be read only
    When I switch the locale to "en_US"
    And I change the "Name" to "My custom name"
    And I switch the locale to "en_GB"
    And I save the product
    Given the following locale accesses:
      | locale | user group | access |
      | en_GB  | Manager    | none   |
    And I refresh current page
    Then the "Name" field should contain "My custom name"
