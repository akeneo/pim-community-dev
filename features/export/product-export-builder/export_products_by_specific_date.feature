Feature: Export products according to a date
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to a date

  @javascript
  Scenario: Export only the products updated since the last export
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath         | %tmp%/product_export/product_export.csv |
      | updated          | last_export                             |
    And the following products:
      | sku      | family   | categories        | price          | size | color    | name-en_US |
      | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    |
      | SNKRS-1R | sneakers | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      """
    When I edit the "SNKRS-1B" product
    And I change the "Weather conditions" to "Hot"
    And I save the product
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;PACK-groups;PACK-products;price-EUR;price-USD;rating;side_view;size;SUBSTITUTION-groups;SUBSTITUTION-products;top_view;UPSELL-groups;UPSELL-products;weather_conditions;X_SELL-groups;X_SELL-products
      SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";;;50.00;70.00;;;45;;;;;;hot;;
      """
