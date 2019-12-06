@javascript
Feature: Export products in XLSX
  In order to be able to access and modify attributes data outside PIM
  As a product manager
  I need to be able to export products in XLSX

  Background:
    Given an "apparel" catalog configuration
    And the following products:
      | sku          | family  | categories                   | price                 | size   | color | manufacturer     | material | country_of_manufacture |
      | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | tshirt-black | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
    And the following product values:
      | product      | attribute       | value                                | locale | scope     |
      | tshirt-white | name            | White t-shirt                        | en_US  |           |
      | tshirt-white | name            | White t-shirt                        | en_GB  |           |
      | tshirt-white | name            | T-shirt blanc                        | fr_FR  |           |
      | tshirt-white | name            | Weißes T-Shirt                       | de_DE  |           |
      | tshirt-white | image           | %fixtures%/SNKRS-1R.png              |        |           |
      | tshirt-white | cost            | 10 EUR, 20 USD, 30 GBP               |        |           |
      | tshirt-white | release_date    | 2016-10-12                           |        | tablet    |
      | tshirt-white | customer_rating | 2                                    |        | tablet    |
      | tshirt-white | handmade        | 1                                    |        |           |
      | tshirt-white | weight          | 5 KILOGRAM                           |        |           |
      | tshirt-white | number_in_stock | 10                                   |        | tablet    |
      | tshirt-white | description     | A stylish white t-shirt              | en_US  | tablet    |
      | tshirt-white | description     | Un T-shirt blanc élégant             | fr_FR  | ecommerce |
      | tshirt-white | description     | A really stylish white t-shirt       | en_US  | print     |
      | tshirt-black | name            | Black t-shirt                        | en_US  |           |
      | tshirt-black | name            | Black t-shirt                        | en_GB  |           |
      | tshirt-black | name            | T-shirt noir                         | fr_FR  |           |
      | tshirt-black | name            | Schwarzes T-Shirt                    | de_DE  |           |
      | tshirt-black | description     | Un T-shirt noir élégant              | fr_FR  | ecommerce |
      | tshirt-black | description     | Ein elegantes schwarzes T-Shirt      | de_DE  | ecommerce |
      | tshirt-black | description     | A really stylish black t-shirt       | en_US  | print     |
      | tshirt-black | description     | Ein sehr elegantes schwarzes T-Shirt | de_DE  | print     |
    And I am logged in as "Julia"

  Scenario: Successfully export products in xlsx with a selection of attributes
    Given the following job "xlsx_tablet_product_export" configuration:
      | filters | {"structure":{"locales":["en_US"],"scope":"tablet","attributes":["price","size","color","cost","description","name","image","release_date","weight"]}, "data": []} |
    And the following products:
      | sku           | family  | categories                   | price                 | size   | color  | manufacturer     | material | country_of_manufacture |
      | tshirt-yellow | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | yellow | american_apparel | cotton   | usa                    |
      | tshirt-green  | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_L | green  | american_apparel | cotton   | usa                    |
    And the following product values:
      | product       | attribute       | value                                | locale | scope     |
      | tshirt-yellow | name            | Yellow t-shirt                       | en_US  |           |
      | tshirt-yellow | name            | Yellow t-shirt                       | en_GB  |           |
      | tshirt-yellow | name            | T-shirt blanc                        | fr_FR  |           |
      | tshirt-yellow | name            | Weißes T-Shirt                       | de_DE  |           |
      | tshirt-yellow | image           | %fixtures%/SNKRS-1R.png              |        |           |
      | tshirt-yellow | cost            | 10 EUR, 20 USD, 30 GBP               |        |           |
      | tshirt-yellow | release_date    | 2016-10-12                           |        | tablet    |
      | tshirt-yellow | customer_rating | 2                                    |        | tablet    |
      | tshirt-yellow | handmade        | 1                                    |        |           |
      | tshirt-yellow | weight          | 5 KILOGRAM                           |        |           |
      | tshirt-yellow | number_in_stock | 10                                   |        | tablet    |
      | tshirt-yellow | description     | A stylish yellow t-shirt             | en_US  | tablet    |
      | tshirt-yellow | description     | Un T-shirt blanc élégant             | fr_FR  | ecommerce |
      | tshirt-yellow | description     | A really stylish yellow t-shirt      | en_US  | print     |
      | tshirt-green  | name            | Green t-shirt                        | en_US  |           |
      | tshirt-green  | name            | Green t-shirt                        | en_GB  |           |
      | tshirt-green  | name            | T-shirt noir                         | fr_FR  |           |
      | tshirt-green  | name            | Schwarzes T-Shirt                    | de_DE  |           |
      | tshirt-green  | description     | Un T-shirt noir élégant              | fr_FR  | ecommerce |
      | tshirt-green  | description     | Ein elegantes schwarzes T-Shirt      | de_DE  | ecommerce |
      | tshirt-green  | description     | A really stylish green t-shirt       | en_US  | print     |
      | tshirt-green  | description     | Ein sehr elegantes schwarzes T-Shirt | de_DE  | print     |
    When I am on the "xlsx_tablet_product_export" export job page
    And I launch the export job
    And I wait for the "xlsx_tablet_product_export" job to finish
    And exported xlsx file of "xlsx_tablet_product_export" should contain:
      | sku           | categories                 | color  | cost-EUR | cost-GBP | cost-USD | description-en_GB-tablet | description-en_US-tablet | enabled | family  | groups | image                                  | name-en_GB     | name-en_US     | price-EUR | price-GBP | price-USD | release_date-tablet | size   | weight | weight-unit |
      | tshirt-white  | men_2013,men_2014,men_2015 | white  | 10.00    | 20.00    | 30.00    |                          | A stylish white t-shirt  | 1       | tshirts |        | files/tshirt-white/image/SNKRS-1R.png  | White t-shirt  | White t-shirt  | 10.00     | 9.00      | 15.00     | 2016-10-12          |        | 5      | KILOGRAM    |
      | tshirt-black  | men_2013,men_2014,men_2015 | black  |          |          |          |                          |                          | 1       | tshirts |        |                                        | Black t-shirt  | Black t-shirt  | 10.00     | 9.00      | 15.00     |                     |        |        |             |
      | tshirt-yellow | men_2013,men_2014,men_2015 | yellow | 10.00    | 20.00    | 30.00    |                          | A stylish yellow t-shirt | 1       | tshirts |        | files/tshirt-yellow/image/SNKRS-1R.png | Yellow t-shirt | Yellow t-shirt | 10.00     | 9.00      | 15.00     | 2016-10-12          | size_M | 5      | KILOGRAM    |
      | tshirt-green  | men_2013,men_2014,men_2015 | green  |          |          |          |                          |                          | 1       | tshirts |        |                                        | Green t-shirt  | Green t-shirt  | 10.00     | 9.00      | 15.00     |                     | size_L |        |             |
