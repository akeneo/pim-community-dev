@javascript
Feature: Quick export products with user permissions applied
  In order to quick export a set of products
  As a redactor
  I need to be able to quick export product and see only granted data

  Scenario: Restrict product data in quick export according to locales and attribute groups permissions
    Given a "clothing" catalog configuration
    And I am logged in as "Mary"
    And I am on the "de_DE" locale page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view product information | IT support |
    And I save the channel
    And I am on the "Other" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support |
    And I save the attribute group
    And the following "sleeve_color" attribute reference data: black, green
    And the following products:
      | sku         | family  | name-en_US   | name-de_DE       | sleeve_color |
      | blackhoodie | hoodies | Black hoodie | Schwarzer Hoodie | black        |
      | greenhoodie | hoodies | Green hoodie | Grüner Hoodie    | green        |
    When I am on the products page
    And I select rows blackhoodie and greenhoodie
    And I press "CSV (All attributes)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_quick_export" quick export to finish
    And exported file of "csv_product_quick_export" should contain:
    """
    sku;categories;description-en_US-mobile;description-fr_FR-mobile;enabled;family;groups;manufacturer;name-en_US;name-fr_FR;price-EUR;price-USD;side_view;size;top_view
    blackhoodie;;;;1;hoodies;;;"Black hoodie";;;;;;
    greenhoodie;;;;1;hoodies;;;"Green hoodie";;;;;;
    """

  Scenario: Restrict published product data in quick export according to locales and attribute groups permissions
    Given a "clothing" catalog configuration
    And I am logged in as "Mary"
    And I am on the "de_DE" locale page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view product information | IT support |
    And I save the channel
    And I am on the "Other" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support |
    And I save the attribute group
    And the following "sleeve_color" attribute reference data: black, green
    And the following published products:
      | sku         | family  | name-en_US   | name-de_DE       | sleeve_color |
      | blackhoodie | hoodies | Black hoodie | Schwarzer Hoodie | black        |
      | greenhoodie | hoodies | Green hoodie | Grüner Hoodie    | green        |
    When I am on the published page
    And I select rows blackhoodie and greenhoodie
    And I press "CSV (All attributes)" on the "Quick Export" dropdown button
    And I wait for the "csv_published_product_quick_export" quick export to finish
    And exported file of "csv_published_product_quick_export" should contain:
    """
    sku;categories;description-en_US-mobile;description-fr_FR-mobile;enabled;family;groups;manufacturer;name-en_US;name-fr_FR;price-EUR;price-USD;side_view;size;top_view
    blackhoodie;;;;1;hoodies;;;"Black hoodie";;;;;;
    greenhoodie;;;;1;hoodies;;;"Green hoodie";;;;;;
    """
