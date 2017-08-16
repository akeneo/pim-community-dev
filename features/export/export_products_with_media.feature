@javascript
Feature: Export products with media
  In order to re-use the images and documents I have set on my products
  As a product manager
  I need to be able to export them along with the products

  Background:
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"

  Scenario: Successfully export products with media
    Given the following products:
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
    And I am on the "csv_footwear_product_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
    SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
    SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;files/SNKRS-1R/side_view/SNKRS-1R.png;45;;
    SNKRS-1C;summer_collection;charcoal;;1;sneakers;;;;"Model 1";55.00;75.00;;files/SNKRS-1C/side_view/SNKRS-1C-s.png;45;files/SNKRS-1C/top_view/SNKRS-1C-t.png;
    """
    And export directory of "csv_footwear_product_export" should contain the following media:
      | files/SNKRS-1R/side_view/SNKRS-1R.png   |
      | files/SNKRS-1C/side_view/SNKRS-1C-s.png |
      | files/SNKRS-1C/top_view/SNKRS-1C-t.png  |

  @jira https://akeneo.atlassian.net/browse/PIM-3785
  Scenario: Successfully export products with nullable media
    Given the following attributes:
      | code    | label-en_US | type              | allowed_extensions | group |
      | picture | Picture     | pim_catalog_image | jpg                | other |
    And the following family:
      | code     | requirements-tablet | requirements-mobile | attributes |
      | flipflop | sku                 | sku                 | picture    |
    And the following products:
      | sku         | categories        | price          | size | color    | name-en_US | family   |
      | FLIPFLOP-1R | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    | flipflop |
      | FLIPFLOP-1C | summer_collection | 55 EUR, 75 USD | 45   | charcoal | Model 1    | flipflop |
      | FLIPFLOP-1B | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    | flipflop |
    And I am on the products grid
    And I select rows FLIPFLOP-1R and FLIPFLOP-1C
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Picture attribute
    And I attach file "akeneo.jpg" to "Picture"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the file "picture" of products FLIPFLOP-1R and FLIPFLOP-1C should be "akeneo.jpg"
    And I am on the products grid
    And I select row FLIPFLOP-1C
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Picture attribute
    And I attach file "" to "Picture"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    And I launched the completeness calculator
    And I am on the "csv_footwear_product_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;color;enabled;family;groups;name-en_US;picture;price-EUR;price-USD;size
    FLIPFLOP-1R;summer_collection;red;1;flipflop;;"Model 1";files/FLIPFLOP-1R/picture/akeneo.jpg;50.00;70.00;45
    FLIPFLOP-1C;summer_collection;charcoal;1;flipflop;;"Model 1";;55.00;75.00;45
    FLIPFLOP-1B;summer_collection;black;1;flipflop;;"Model 1";;50.00;70.00;45
    """
    And export directory of "csv_footwear_product_export" should contain the following media:
      | files/FLIPFLOP-1R/picture/akeneo.jpg |

  @jira https://akeneo.atlassian.net/browse/PIM-5844
  Scenario: Successfully export products with media with identifier containing slash
    Given the following products:
      | sku       | family   | categories        | price          | size | color    | name-en_US |
      | SN/KRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    |
    And the following product values:
      | product   | attribute | value                    |
      | SN/KRS-1B | side_view | %fixtures%/SNKRS-1R.png |
    And I launched the completeness calculator
    And I am on the "csv_footwear_product_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
    SN/KRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;files/SN_KRS-1B/side_view/SNKRS-1R.png;45;;
    """
    And export directory of "csv_footwear_product_export" should contain the following media:
      | files/SN_KRS-1B/side_view/SNKRS-1R.png |
