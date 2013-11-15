@javascript
Feature: Export media with products
  In order to re-use the images and documents I have set on my products
  As Julia
  I need to be able to export them along with the products

  Scenario: Successfully export media
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family   | categories        |
      | SNKRS-1B | sneakers | summer_collection |
      | SNKRS-1R | sneakers | summer_collection |
      | SNKRS-1C | sneakers | summer_collection |
    And the following product values:
      | product  | attribute | value          |
      | SNKRS-1B | Name      | Model 1        |
      | SNKRS-1B | Price     | 50 EUR, 70 USD |
      | SNKRS-1B | Size      | 45             |
      | SNKRS-1B | Color     | black          |
      | SNKRS-1B | side_view |                |
      | SNKRS-1R | Name      | Model 1        |
      | SNKRS-1R | Price     | 50 EUR, 70 USD |
      | SNKRS-1R | Size      | 45             |
      | SNKRS-1R | Color     | red            |
      | SNKRS-1R | side_view | SNKRS-1R.png   |
      | SNKRS-1C | Name      | Model 1        |
      | SNKRS-1C | Price     | 55 EUR, 75 USD |
      | SNKRS-1C | Size      | 45             |
      | SNKRS-1C | Color     | charcoal       |
      | SNKRS-1C | side_view | SNKRS-1C-s.png |
      | SNKRS-1C | top_view  | SNKRS-1C-t.png |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    And I am on the "footwear_product_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then exported file of "footwear_product_export" should contain:
    """
    sku;family;groups;categories;name;price;size;color;side_view;top_view
    SNKRS-1B;sneakers;;summer_collection;"Model 1";"50 EUR, 70 USD";45;black;;
    SNKRS-1R;sneakers;;summer_collection;"Model 1";"50 EUR, 70 USD";45;red;/SNKRS-1R/side_view/SNKRS-1R.png;
    SNKRS-1C;sneakers;;summer_collection;"Model 1";"55 EUR, 75 USD";45;charcoal;/SNKRS-1C/side_view/SNKRS-1C-s.png;/SNKRS-1C/top_view/SNKRS-1C-t.png

    """
    And export directory of "footwear_product_export" should contain the following media:
      | files/SNKRS-1R/side_view/SNKRS-1R.png   |
      | files/SNKRS-1C/side_view/SNKRS-1C-s.png |
      | files/SNKRS-1C/top_view/SNKRS-1C-t.png  |
