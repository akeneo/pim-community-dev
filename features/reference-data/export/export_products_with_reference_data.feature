@javascript
Feature: Export products
  In order to use the reference data
  As a product manager
  I need to be able to export the products that have reference data

  Scenario: Export products with reference data
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am logged in as "Julia"
    And the following "sole_color" attribute reference data: Red, Blue and Green
    And the following "sole_fabric" attribute reference data: Cashmerewool, Neoprene and Silk
    Given the following products:
      | sku      | family   | categories        | price          | size | color    | name-en_US |
      | SNKRS-1B | heels    | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    |
      | SNKRS-1R | heels    | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    |
      | SNKRS-1C | heels    | summer_collection | 55 EUR, 75 USD | 45   | charcoal | Model 1    |
    And the following product values:
      | product  | attribute   | value          |
      | SNKRS-1B | heel_color  | Red            |
      | SNKRS-1B | sole_fabric | Silk           |
      | SNKRS-1B | sole_color  | Blue           |
      | SNKRS-1R | heel_color  | Red            |
      | SNKRS-1R | sole_fabric | Neoprene, Silk |
      | SNKRS-1R | sole_color  | Red            |
    And I launched the completeness calculator
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;categories;enabled;family;groups;color;description-en_US-mobile;heel_color;manufacturer;name-en_US;price-EUR;price-USD;side_view;size;sole_color;sole_fabric;top_view
    SNKRS-1B;summer_collection;1;heels;;black;;Red;;Model 1;50.00;70.00;;45;Blue;Silk;
    SNKRS-1R;summer_collection;1;heels;;red;;Red;;Model 1;50.00;70.00;;45;Red;Neoprene,Silk;
    """
