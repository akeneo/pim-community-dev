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
    And I am on the "ecommerce_product_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;name-en_US;name-en_GB;name-fr_FR;name-de_DE;description-en_US;description-en_GB;description-fr_FR;description-de_DE;price;size;color;manufacturer;country_of_manufacture;material;categories;enabled;family;groups;additional_colors;cost;customer_rating;customs_tax;handmade;image;number_in_stock;release_date;thumbnail;washing_temperature;weight
    tshirt-white;"White t-shirt";"White t-shirt";"T-shirt blanc";"Weißes T-Shirt";"A stylish white t-shirt";"An elegant white t-shirt";"Un T-shirt blanc élégant";"Ein elegantes weißes T-Shirt";"10.00 EUR,15.00 USD,9.00 GBP";size_M;white;american_apparel;usa;cotton;"2013_collection,2014_collection,2015_collection";1;tshirts;;;;;;;;;;;;
    tshirt-black;"Black t-shirt";"Black t-shirt";"T-shirt noir";"Schwarzes T-Shirt";"A stylish black t-shirt";"An elegant black t-shirt";"Un T-shirt noir élégant";"Ein elegantes schwarzes T-Shirt";"10.00 EUR,15.00 USD,9.00 GBP";size_L;black;american_apparel;usa;cotton;"2013_collection,2014_collection,2015_collection";1;tshirts;;;;;;;;;;;;
    """
