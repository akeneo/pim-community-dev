@javascript
Feature: Export products according to a date
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to a date

  Scenario: Export only the products updated by the UI since the last export
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/product_export.csv"}                                                                                |
      | filters   | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "updated", "operator": "SINCE LAST JOB", "value": "csv_footwear_product_export"}]} |
      | with_uuid | yes                                                                                                                                                      |
    And the following products:
      | uuid                                 | sku      | family   | categories        | price          | size | color | name-en_US |
      | dff558dd-9b92-46f8-be89-a26a83203834 | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black | Model 1    |
      | 0d2749c9-803a-4b38-8836-519265a5465c | SNKRS-1R | sneakers | summer_collection | 50 EUR, 70 USD | 45   | red   | Model 1    |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      uuid;sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      dff558dd-9b92-46f8-be89-a26a83203834;SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      0d2749c9-803a-4b38-8836-519265a5465c;SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      """
    When I edit the "SNKRS-1B" product
    And I change the "Weather conditions" to "Hot"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      uuid;sku;categories;enabled;family;groups;color;description-en_US-mobile;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      dff558dd-9b92-46f8-be89-a26a83203834;SNKRS-1B;summer_collection;1;sneakers;;black;;;;Model 1;;;;50.00;70.00;;;45;;;;;;;;hot;;;
      """
