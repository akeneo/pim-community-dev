Feature: Deactivate a currency
  In order to use the enriched product data
  As a product manager
  I need to be able to deactivate a currency

  Background:
    Given a "footwear" catalog configuration
    And the following job "footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And the following products:
      | sku      | family   | categories        | price          | size | color | name-en_US |
      | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black | Sneakers   |
    And I am logged in as "Julia"
    When I am on the "tablet" channel page
    And I change the Currencies to "EUR"
    Then I save the channel
    When I am on the currencies page
    And I filter by "Activated" with value "yes"
    Then I deactivate the USD currency

  @javascript
  Scenario: Do not export prices with disable currency
    Given I am on the "footwear_product_export" export job page
    And I launch the export job
    And I wait for the "footwear_product_export" job to finish
    Then exported file of "footwear_product_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;rating;side_view;size;top_view;weather_conditions
    SNKRS-1B;summer_collection;black;;1;sneakers;;;;Sneakers;50.00;;;45;;
    """

  @javascript
  Scenario: Do not export prices with disable currency in quick export
    Given I am on the products page
    And I select rows SNKRS-1B
    Then I press "CSV (All attributes)" on the "Quick Export" dropdown button
    And I wait for the quick export to finish
    Then exported file of "csv_product_quick_export" should contain:
    """
    sku;categories;color;description-en_US-tablet;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;rating;side_view;size;top_view;weather_conditions
    SNKRS-1B;summer_collection;black;;1;sneakers;;;;Sneakers;50.00;;;45;;
    """

  @javascript
  Scenario: Do not show disable currency in the PEF
    Given I am on the "SNKRS-1B" product page
    And I visit the "Marketing" group
    Then I should see the Price in EUR fields
    But I should not see the Price in USD fields
