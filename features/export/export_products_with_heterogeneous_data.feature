@javascript
Feature: Export products
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products to several channels

  Scenario: Successfully export products with heterogeneous data
    Given an "apparel" catalog configuration
    And the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
    And the following products:
      | sku       | family  | categories                   | price                 |
      | my-sandal | sandals | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP |
    And the following product values:
      | product   | attribute   | value                            | locale | scope     |
      | my-sandal | name        | White sandal                     | en_US  |           |
      | my-sandal | name        | White sandal                     | en_GB  |           |
      | my-sandal | name        | Sandale blanche                  | fr_FR  |           |
      | my-sandal | name        | Weißes Sandal                    | de_DE  |           |
      | my-sandal | description | A stylish white sandal           | en_US  | ecommerce |
      | my-sandal | description | An elegant white sandal          | en_GB  | ecommerce |
      | my-sandal | description | Une sandale blanche élégante     | fr_FR  | ecommerce |
      | my-sandal | description | Ein elegantes weißes Sandal      | de_DE  | ecommerce |
      | my-sandal | description | A really stylish white sandal    | en_US  | print     |
      | my-sandal | description | Ein sehr elegantes weißes Sandal | de_DE  | print     |
    And the following products:
      | sku          | family  | categories                   | price                 | size   | color | manufacturer     | material | country_of_manufacture |
      | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | tshirt-black | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
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
    And I launched the completeness calculator
    And I am logged in as "Julia"
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;categories;color;country_of_manufacture;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;price-EUR;price-GBP;price-USD;size
    my-sandal;men_2013,men_2014,men_2015;;;"Ein elegantes weißes Sandal";"An elegant white sandal";"A stylish white sandal";"Une sandale blanche élégante";1;sandals;;;;"Weißes Sandal";"White sandal";"White sandal";"Sandale blanche";10.00;9.00;15.00;
    tshirt-white;men_2013,men_2014,men_2015;white;usa;"Ein elegantes weißes T-Shirt";"An elegant white t-shirt";"A stylish white t-shirt";"Un T-shirt blanc élégant";1;tshirts;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";"White t-shirt";"T-shirt blanc";10.00;9.00;15.00;size_M
    tshirt-black;men_2013,men_2014,men_2015;black;usa;"Ein elegantes schwarzes T-Shirt";"An elegant black t-shirt";"A stylish black t-shirt";"Un T-shirt noir élégant";1;tshirts;;american_apparel;cotton;"Schwarzes T-Shirt";"Black t-shirt";"Black t-shirt";"T-shirt noir";10.00;9.00;15.00;size_L
    """
