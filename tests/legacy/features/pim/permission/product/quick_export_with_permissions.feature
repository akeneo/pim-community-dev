@javascript
Feature: Quick export products with user permissions applied
  In order to quick export a set of products
  As a redactor
  I need to be able to quick export product and see only granted data

  @critical
  Scenario: Restrict localizable product data in quick export according to locales and attribute groups permissions
    Given a "clothing" catalog configuration
    And I am logged in as "Mary"
    And the following products:
      | sku         | family  | name-en_US   | name-de_DE       | sleeve_color |
      | blackhoodie | hoodies | Black hoodie | Schwarzer Hoodie | black        |
      | greenhoodie | hoodies | Green hoodie | Grüner Hoodie    | yellow       |
    And I am on the "de_DE" locale page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view information | IT support |
    And I save the locale
    And I am on the "Other" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support |
    And I save the attribute group
    When I am on the products grid
    And I select rows blackhoodie and greenhoodie
    And I press the "Quick Export" button
    And I press the "CSV" button
    And I press the "All attributes" button
    And I press the "With codes" button
    And I press the "Without media" button
    And I press the "Export" button
    And I wait for the "csv_product_quick_export" quick export to finish
    And first exported file of "csv_product_quick_export" should contain:
    """
    sku;categories;description-en_US-mobile;description-fr_FR-mobile;enabled;family;groups;manufacturer;name-en_US;name-fr_FR;price-EUR;price-USD;side_view;size;top_view
    blackhoodie;;;;1;hoodies;;;"Black hoodie";;;;;;
    greenhoodie;;;;1;hoodies;;;"Green hoodie";;;;;;
    """

  @critical
  Scenario: Restrict published product data in quick export according to locales and attribute groups permissions
    Given a "clothing" catalog configuration
    And I am logged in as "Mary"
    And the following published products:
      | sku         | family  | name-en_US   | name-de_DE       | sleeve_color |
      | blackhoodie | hoodies | Black hoodie | Schwarzer Hoodie | black        |
      | greenhoodie | hoodies | Green hoodie | Grüner Hoodie    | yellow       |
    And I am on the "de_DE" locale page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view information | IT support |
    And I save the locale
    And I am on the "Other" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support |
    And I save the attribute group
    When I am on the published products grid
    And I select rows blackhoodie and greenhoodie
    And I press the "Quick Export" button
    And I press the "CSV" button
    And I press the "All attributes" button
    And I press the "Export" button
    And I wait for the "csv_published_product_quick_export" quick export to finish
    And exported file of "csv_published_product_quick_export" should contain:
    """
    sku;categories;description-en_US-mobile;description-fr_FR-mobile;enabled;family;groups;manufacturer;name-en_US;name-fr_FR;price-EUR;price-USD;side_view;size;top_view
    blackhoodie;;;;1;hoodies;;;"Black hoodie";;;;;;
    greenhoodie;;;;1;hoodies;;;"Green hoodie";;;;;;
    """
