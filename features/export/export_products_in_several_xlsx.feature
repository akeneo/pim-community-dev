@javascript
Feature: Export products
  In order to use the enriched product data
  As a product manager
  I need to be able to export products to several channels

  Background:
    Given an "apparel" catalog configuration
    And the following products:
      | sku           | family  | categories                   | price                 |
      | sandal-white  | sandals | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP |
      | sandal-black  | sandals | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP |
      | sandal-yellow | sandals | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP |
      | sandal-green  | sandals | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP |
      | sandal-grey   | sandals | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP |
    And the following product values:
      | product       | attribute   | value           | locale | scope  |
      | sandal-white  | description | White sandal    | en_US  | tablet |
      | sandal-black  | description | Black sandal    | en_US  | tablet |
      | sandal-yellow | description | Yellow sandal   | en_US  | tablet |
      | sandal-green  | description | Green sandal    | en_US  | tablet |
      | sandal-grey   | description | Grey sandal     | en_US  | tablet |
      | sandal-white  | description | White sandal    | en_GB  | tablet |
      | sandal-black  | description | Black sandal    | en_GB  | tablet |
      | sandal-yellow | description | Yellow sandal   | en_GB  | tablet |
      | sandal-green  | description | Green sandal    | en_GB  | tablet |
      | sandal-grey   | description | Grey sandal     | en_GB  | tablet |
      | sandal-white  | name        | White sandal    | en_US  |        |
      | sandal-black  | name        | Black sandal    | en_US  |        |
      | sandal-yellow | name        | Yellow sandal   | en_US  |        |
      | sandal-green  | name        | Green sandal    | en_US  |        |
      | sandal-grey   | name        | Grey sandal     | en_US  |        |
      | sandal-white  | name        | White sandal    | en_GB  |        |
      | sandal-black  | name        | Black sandal    | en_GB  |        |
      | sandal-yellow | name        | Yellow sandal   | en_GB  |        |
      | sandal-green  | name        | Green sandal    | en_GB  |        |
      | sandal-grey   | name        | Grey sandal     | en_GB  |        |

  Scenario: Successfully export products to into several files
    Given the following job "xlsx_tablet_product_export" configuration:
      | filePath      | %tmp%/xlsx_tablet_product_export/xlsx_tablet_product_export.xlsx |
      | linesPerFiles | 3                                                                |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    And I am on the "xlsx_tablet_product_export" export job page
    And I launch the export job
    And I wait for the "xlsx_tablet_product_export" job to finish
    When I press the "Download generated files" button
    Then I should see the text "xlsx_tablet_product_export_1.xlsx"
    And I should see the text "xlsx_tablet_product_export_2.xlsx"
    And exported xlsx file of "xlsx_tablet_product_export" should contain:
      | sku           | categories                 | description-en_GB-tablet | description-en_US-tablet | enabled | family  | name-en_GB   | name-en_US   | price-EUR | price-GBP | price-USD |
      | sandal-white  | men_2013,men_2014,men_2015 | White sandal             | White sandal             | 1       | tshirts | White sandal | White sandal | 10.00     | 9.00      | 15.00     |
      | sandal-black  | men_2013,men_2014,men_2015 | Black sandal             | Black sandal             | 1       | tshirts | Black sandal | Black sandal | 10.00     | 9.00      | 15.00     |




    Then I wait 5000 seconds
