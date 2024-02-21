@javascript
Feature: Export products
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products to several channels

  @validate-migration
  Scenario: Successfully export products to multiple channels
    Given an "apparel" catalog configuration
    And the following job "ecommerce_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/ecommerce_product_export/ecommerce_product_export.csv"} |
      | with_uuid | yes                                                                                           |
    And the following job "tablet_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/tablet_product_export/tablet_product_export.csv"} |
      | with_uuid | yes                                                                                     |
    And the following job "print_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/print_product_export/print_product_export.csv"} |
      | with_uuid | yes                                                                                   |
    And the following products:
      | uuid                                 | sku          | family  | categories                   | price                 | size   | color | manufacturer     | material | country_of_manufacture |
      | 850ea968-464f-44a7-a04b-f84eeb4ba4c0 | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | 92a17589-6452-4769-bdba-4caf0c0942be | tshirt-black | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
    And the following product values:
      | product      | attribute   | value                                | locale | scope     |
      | tshirt-white | name        | White t-shirt                        | en_US  |           |
      | tshirt-white | name        | White t-shirt                        | en_GB  |           |
      | tshirt-white | name        | T-shirt blanc                        | fr_FR  |           |
      | tshirt-white | name        | Weißes T-Shirt                       | de_DE  |           |
      | tshirt-white | description | A stylish white t-shirt              | en_US  | ecommerce |
      | tshirt-white | description | An elegant white t-shirt             | en_GB  | ecommerce |
      | tshirt-white | description | Un T-shirt blanc élégant             | fr_FR  | ecommerce |
      | tshirt-white | description | Ein elegantes weißes T-Shirt         | de_DE  | ecommerce |
      | tshirt-white | description | A really stylish white t-shirt       | en_US  | print     |
      | tshirt-white | description | Ein sehr elegantes weißes T-Shirt    | de_DE  | print     |
      | tshirt-black | name        | Black t-shirt                        | en_US  |           |
      | tshirt-black | name        | Black t-shirt                        | en_GB  |           |
      | tshirt-black | name        | T-shirt noir                         | fr_FR  |           |
      | tshirt-black | name        | Schwarzes T-Shirt                    | de_DE  |           |
      | tshirt-black | description | A stylish black t-shirt              | en_US  | ecommerce |
      | tshirt-black | description | An elegant black t-shirt             | en_GB  | ecommerce |
      | tshirt-black | description | Un T-shirt noir élégant              | fr_FR  | ecommerce |
      | tshirt-black | description | Ein elegantes schwarzes T-Shirt      | de_DE  | ecommerce |
      | tshirt-black | description | A really stylish black t-shirt       | en_US  | print     |
      | tshirt-black | description | Ein sehr elegantes schwarzes T-Shirt | de_DE  | print     |
    And I am logged in as "Julia"
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then I should see the text "Read 2"
    And I should see the text "Written 2"
    Then exported file of "ecommerce_product_export" should contain:
    """
    uuid;sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE-EUR;customs_tax-de_DE-GBP;customs_tax-de_DE-USD;datasheet;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;handmade;image;legend-de_DE;legend-en_GB;legend-en_US;legend-fr_FR;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price-EUR;price-GBP;price-USD;release_date-ecommerce;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    850ea968-464f-44a7-a04b-f84eeb4ba4c0;tshirt-white;;men_2013,men_2014,men_2015;white;;;;usa;;;;;;"Ein elegantes weißes T-Shirt";"An elegant white t-shirt";"A stylish white t-shirt";"Un T-shirt blanc élégant";1;tshirts;;;;;;;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";"White t-shirt";"T-shirt blanc";;10.00;9.00;15.00;;size_M;;;;;
    92a17589-6452-4769-bdba-4caf0c0942be;tshirt-black;;men_2013,men_2014,men_2015;black;;;;usa;;;;;;"Ein elegantes schwarzes T-Shirt";"An elegant black t-shirt";"A stylish black t-shirt";"Un T-shirt noir élégant";1;tshirts;;;;;;;;american_apparel;cotton;"Schwarzes T-Shirt";"Black t-shirt";"Black t-shirt";"T-shirt noir";;10.00;9.00;15.00;;size_L;;;;;
    """
    When I am on the "tablet_product_export" export job page
    And I launch the export job
    And I wait for the "tablet_product_export" job to finish
    Then exported file of "tablet_product_export" should contain:
    """
    uuid;sku;additional_colors;categories;color;cost-GBP;cost-USD;country_of_manufacture;customer_rating-tablet;datasheet;description-en_GB-tablet;description-en_US-tablet;enabled;family;groups;handmade;image;legend-en_GB;legend-en_US;manufacturer;material;name-en_GB;name-en_US;number_in_stock-tablet;price-EUR;price-GBP;price-USD;release_date-tablet;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    850ea968-464f-44a7-a04b-f84eeb4ba4c0;tshirt-white;;men_2013,men_2014,men_2015;white;;;usa;;;;;1;tshirts;;;;;;american_apparel;cotton;"White t-shirt";"White t-shirt";;10.00;9.00;15.00;;size_M;;;;;
    92a17589-6452-4769-bdba-4caf0c0942be;tshirt-black;;men_2013,men_2014,men_2015;black;;;usa;;;;;1;tshirts;;;;;;american_apparel;cotton;"Black t-shirt";"Black t-shirt";;10.00;9.00;15.00;;size_L;;;;;
    """
    When I am on the "print_product_export" export job page
    And I launch the export job
    And I wait for the "print_product_export" job to finish
    Then exported file of "print_product_export" should contain:
    """
    uuid;sku;additional_colors;categories;color;cost-EUR;cost-USD;country_of_manufacture;customer_rating-print;customs_tax-de_DE-EUR;customs_tax-de_DE-USD;datasheet;description-de_DE-print;description-en_US-print;enabled;family;groups;handmade;image;legend-de_DE;legend-en_US;manufacturer;material;name-de_DE;name-en_US;number_in_stock-print;price-EUR;price-GBP;price-USD;release_date-print;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    850ea968-464f-44a7-a04b-f84eeb4ba4c0;tshirt-white;;men_2013,men_2014,men_2015;white;;;usa;;;;;;"Ein sehr elegantes weißes T-Shirt";"A really stylish white t-shirt";1;tshirts;;;;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";;10.00;9.00;15.00;;size_M;;;;;
    92a17589-6452-4769-bdba-4caf0c0942be;tshirt-black;;men_2013,men_2014,men_2015;black;;;usa;;;;;;"Ein sehr elegantes schwarzes T-Shirt";"A really stylish black t-shirt";1;tshirts;;;;;american_apparel;cotton;"Schwarzes T-Shirt";"Black t-shirt";;10.00;9.00;15.00;;size_L;;;;;
    """
