@javascript
Feature: Export products with media
  In order to re-use the images and documents I have set on my products
  As a product manager
  I need to be able to export them along with the products

  Background:
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/product_export.csv"} |
      | with_uuid | yes                                                                       |
    And I am logged in as "Julia"

  Scenario: Successfully export products with media
    Given the following products:
      | uuid                                 | sku      | family   | categories        | price          | size | color    | name-en_US |
      | 7c6ed07f-4a18-484f-935d-427985d55d92 | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    |
      | 1e54c0a3-6c71-42bd-a038-c0d7dfaff0a2 | SNKRS-1R | sneakers | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    |
      | 0f733194-b6b6-4eac-ad3c-824100e6aa69 | SNKRS-1C | sneakers | summer_collection | 55 EUR, 75 USD | 45   | charcoal | Model 1    |
    And the following product values:
      | product  | attribute | value                     |
      | SNKRS-1R | side_view | %fixtures%/SNKRS-1R.png   |
      | SNKRS-1C | side_view | %fixtures%/SNKRS-1C-s.png |
      | SNKRS-1C | top_view  | %fixtures%/SNKRS-1C-t.png |
    And I am on the "csv_footwear_product_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      uuid;sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      7c6ed07f-4a18-484f-935d-427985d55d92;SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      1e54c0a3-6c71-42bd-a038-c0d7dfaff0a2;SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;files/SNKRS-1R/side_view/SNKRS-1R.png;45;;
      0f733194-b6b6-4eac-ad3c-824100e6aa69;SNKRS-1C;summer_collection;charcoal;;1;sneakers;;;;"Model 1";55.00;75.00;;files/SNKRS-1C/side_view/SNKRS-1C-s.png;45;files/SNKRS-1C/top_view/SNKRS-1C-t.png;
      """
    And export directory of "csv_footwear_product_export" should contain the following media:
      | files/SNKRS-1R/side_view/SNKRS-1R.png   |
      | files/SNKRS-1C/side_view/SNKRS-1C-s.png |
      | files/SNKRS-1C/top_view/SNKRS-1C-t.png  |
