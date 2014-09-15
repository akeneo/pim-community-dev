@javascript
Feature: Export products with media
  In order to re-use the images and documents I have set on my products
  As a product manager
  I need to be able to export them along with the products

  Scenario: Successfully export products with media
    Given a "footwear" catalog configuration
    And the following job "footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And the following products:
      | sku      | family   | categories        | price          | size | color    | name-en_US |
      | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    |
      | SNKRS-1R | sneakers | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    |
      | SNKRS-1C | sneakers | summer_collection | 55 EUR, 75 USD | 45   | charcoal | Model 1    |
    And the following product values:
      | product  | attribute | value                     |
      | SNKRS-1R | side_view | %fixtures%/SNKRS-1R.png   |
      | SNKRS-1C | side_view | %fixtures%/SNKRS-1C-s.png |
      | SNKRS-1C | top_view  | %fixtures%/SNKRS-1C-t.png |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    And I am on the "footwear_product_export" export job page
    When I launch the export job
    And I wait for the "footwear_product_export" job to finish
    Then exported file of "footwear_product_export" should contain:
    """
    sku;family;groups;categories;color;description-en_US-mobile;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions;enabled
    SNKRS-1B;sneakers;;summer_collection;black;;;;"Model 1";50.00;70.00;;;45;;;1
    SNKRS-1R;sneakers;;summer_collection;red;;;;"Model 1";50.00;70.00;;files/SNKRS-1R/side_view/SNKRS-1R.png;45;;;1
    SNKRS-1C;sneakers;;summer_collection;charcoal;;;;"Model 1";55.00;75.00;;files/SNKRS-1C/side_view/SNKRS-1C-s.png;45;files/SNKRS-1C/top_view/SNKRS-1C-t.png;;1

    """
    And export directory of "footwear_product_export" should contain the following media:
      | files/SNKRS-1R/side_view/SNKRS-1R.png   |
      | files/SNKRS-1C/side_view/SNKRS-1C-s.png |
      | files/SNKRS-1C/top_view/SNKRS-1C-t.png  |
