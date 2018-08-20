@javascript
Feature: Export products without media
  In order to re-import the product
  As a product manager
  I need to be able to export them without media

  Background:
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath   | %tmp%/product_export/product_export.csv |
      | with_media | no                                      |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-5928
  Scenario: Successfully export products without media
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
    sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;size;weather_conditions
    SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;45;
    SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;45;
    SNKRS-1C;summer_collection;charcoal;;1;sneakers;;;;"Model 1";55.00;75.00;;45;
    """
