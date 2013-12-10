@javascript
Feature: Export products
  In order to use the enriched product data
  As Julia
  I need to be able to export the products to several channels

  Scenario: Successfully export products to multiple channels
    Given an "apparel" catalog configuration
    And the following products:
      | sku          | family  | categories                                           |
      | tshirt-white | tshirts | 2013_collection, 2014_collection and 2015_collection |
      | tshirt-black | tshirts | 2013_collection, 2014_collection and 2015_collection |
    And the following product values:
      | product      | attribute              | value                                | locale | scope     |
      | tshirt-white | Name                   | White t-shirt                        | en_US  |           |
      | tshirt-white | Name                   | White t-shirt                        | en_GB  |           |
      | tshirt-white | Name                   | T-shirt blanc                        | fr_FR  |           |
      | tshirt-white | Name                   | Weißes T-Shirt                       | de_DE  |           |
      | tshirt-white | Description            | A stylish white t-shirt              | en_US  | ecommerce |
      | tshirt-white | Description            | An elegant white t-shirt             | en_GB  | ecommerce |
      | tshirt-white | Description            | Un T-shirt blanc élégant             | fr_FR  | ecommerce |
      | tshirt-white | Description            | Ein elegantes weißes T-Shirt         | de_DE  | ecommerce |
      | tshirt-white | Description            | A really stylish white t-shirt       | en_US  | print     |
      | tshirt-white | Description            | Ein sehr elegantes weißes T-Shirt    | de_DE  | print     |
      | tshirt-white | Price                  | 10 EUR, 15 USD, 9 GBP                |        |           |
      | tshirt-white | Size                   | size_M                               |        |           |
      | tshirt-white | Color                  | white                                |        |           |
      | tshirt-white | Manufacturer           | american_apparel                     |        |           |
      | tshirt-white | country_of_manufacture | usa                                  |        |           |
      | tshirt-white | material               | cotton                               |        |           |
      | tshirt-black | Name                   | Black t-shirt                        | en_US  |           |
      | tshirt-black | Name                   | Black t-shirt                        | en_GB  |           |
      | tshirt-black | Name                   | T-shirt noir                         | fr_FR  |           |
      | tshirt-black | Name                   | Schwarzes T-Shirt                    | de_DE  |           |
      | tshirt-black | Description            | A stylish black t-shirt              | en_US  | ecommerce |
      | tshirt-black | Description            | An elegant black t-shirt             | en_GB  | ecommerce |
      | tshirt-black | Description            | Un T-shirt noir élégant              | fr_FR  | ecommerce |
      | tshirt-black | Description            | Ein elegantes schwarzes T-Shirt      | de_DE  | ecommerce |
      | tshirt-black | Description            | A really stylish black t-shirt       | en_US  | print     |
      | tshirt-black | Description            | Ein sehr elegantes schwarzes T-Shirt | de_DE  | print     |
      | tshirt-black | Price                  | 10 EUR, 15 USD, 9 GBP                |        |           |
      | tshirt-black | Size                   | size_L                               |        |           |
      | tshirt-black | Color                  | black                                |        |           |
      | tshirt-black | Manufacturer           | american_apparel                     |        |           |
      | tshirt-black | country_of_manufacture | usa                                  |        |           |
      | tshirt-black | material               | cotton                               |        |           |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;family;groups;categories;additional_colors;color;cost;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;handmade;image;legend-de_DE;legend-en_GB;legend-en_US;legend-fr_FR;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price;release_date-ecommerce;size;thumbnail;washing_temperature;weight;enabled
    tshirt-white;tshirts;;2013_collection,2014_collection,2015_collection;;white;;usa;;;"Ein elegantes weißes T-Shirt";"An elegant white t-shirt";"A stylish white t-shirt";"Un T-shirt blanc élégant";;;;;;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";"White t-shirt";"T-shirt blanc";;"10.00 EUR,9.00 GBP,15.00 USD";;size_M;;;;1
    tshirt-black;tshirts;;2013_collection,2014_collection,2015_collection;;black;;usa;;;"Ein elegantes schwarzes T-Shirt";"An elegant black t-shirt";"A stylish black t-shirt";"Un T-shirt noir élégant";;;;;;;american_apparel;cotton;"Schwarzes T-Shirt";"Black t-shirt";"Black t-shirt";"T-shirt noir";;"10.00 EUR,9.00 GBP,15.00 USD";;size_L;;;;1
    """
    When I am on the "tablet_product_export" export job page
    And I launch the export job
    And I wait for the job to finish
    Then exported file of "tablet_product_export" should contain:
    """
    sku;family;groups;categories;additional_colors;color;cost;country_of_manufacture;customer_rating-tablet;description-en_GB-tablet;description-en_US-tablet;handmade;image;legend-en_GB;legend-en_US;manufacturer;material;name-en_GB;name-en_US;number_in_stock-tablet;price;release_date-tablet;size;thumbnail;washing_temperature;weight;enabled
    tshirt-white;tshirts;;2013_collection,2014_collection,2015_collection;;white;;usa;;;;;;;;american_apparel;cotton;"White t-shirt";"White t-shirt";;"10.00 EUR,9.00 GBP,15.00 USD";;size_M;;;;1
    tshirt-black;tshirts;;2013_collection,2014_collection,2015_collection;;black;;usa;;;;;;;;american_apparel;cotton;"Black t-shirt";"Black t-shirt";;"10.00 EUR,9.00 GBP,15.00 USD";;size_L;;;;1
    """
    When I am on the "print_product_export" export job page
    And I launch the export job
    And I wait for the job to finish
    Then exported file of "print_product_export" should contain:
    """
    sku;family;groups;categories;additional_colors;color;cost;country_of_manufacture;customer_rating-print;customs_tax-de_DE;description-de_DE-print;description-en_US-print;handmade;image;legend-de_DE;legend-en_US;manufacturer;material;name-de_DE;name-en_US;number_in_stock-print;price;release_date-print;size;thumbnail;washing_temperature;weight;enabled
    tshirt-white;tshirts;;2013_collection,2014_collection,2015_collection;;white;;usa;;;"Ein sehr elegantes weißes T-Shirt";"A really stylish white t-shirt";;;;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";;"10.00 EUR,9.00 GBP,15.00 USD";;size_M;;;;1
    tshirt-black;tshirts;;2013_collection,2014_collection,2015_collection;;black;;usa;;;"Ein sehr elegantes schwarzes T-Shirt";"A really stylish black t-shirt";;;;;american_apparel;cotton;"Schwarzes T-Shirt";"Black t-shirt";;"10.00 EUR,9.00 GBP,15.00 USD";;size_L;;;;1
    """
