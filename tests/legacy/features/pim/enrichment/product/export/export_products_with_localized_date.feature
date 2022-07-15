@javascript
Feature: Export products with localized dates
  In order to use the enriched product data
  As a product manager
  I need to be able to export the localized products to several channels

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And I am on the "sandals" family page
    And I visit the "Attributes" tab
    And I add available attribute Release date
    And I save the family
    And the following products:
      | sku            | family   | categories                   |
      | sweater-white  | tshirts  | men_2013, men_2014, men_2015 |
      | sweater-yellow | tshirts  | men_2013, men_2014, men_2015 |
    And the following product values:
      | product        | attribute    | value                        | locale | scope     |
      | sweater-white  | name         | TShirt blanche               | fr_FR  |           |
      | sweater-white  | name         | Weißes TShirt                | de_DE  |           |
      | sweater-white  | description  | Un TShirt blanche élégante   | fr_FR  | ecommerce |
      | sweater-white  | description  | Ein elegantes weißes TShirt  | de_DE  | ecommerce |
      | sweater-white  | price        | 10.90 EUR,15 USD,9 GBP       |        |           |
      | sweater-white  | size         | size_M                       |        |           |
      | sweater-white  | color        | red                          |        |           |
      | sweater-white  | manufacturer | american_apparel             |        |           |
      | sweater-white  | material     | leather                      |        |           |
      | sweater-white  | release_date | 1999-10-28                   |        | ecommerce |
      | sweater-yellow | name         | TShirt jaune                 | fr_FR  |           |
      | sweater-yellow | name         | Gelb TShirt                  | de_DE  |           |
      | sweater-yellow | description  | Un TShirt jaune élégant      | fr_FR  | ecommerce |
      | sweater-yellow | description  | Ein elegantes gelb TShirt    | de_DE  | ecommerce |
      | sweater-yellow | price        | 10.90 EUR,15 USD,9 GBP       |        |           |
      | sweater-yellow | price        | 10.90 EUR,15 USD,9 GBP       |        |           |
      | sweater-yellow | size         | size_M                       |        |           |
      | sweater-yellow | color        | red                          |        |           |
      | sweater-yellow | manufacturer | american_apparel             |        |           |
      | sweater-yellow | material     | leather                      |        |           |

  Scenario: Export dates attributes in a specified format
    Given the following job "ecommerce_product_export" configuration:
      | filePath   | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
      | dateFormat | dd/MM/yyyy                                                  |
    When I am on the "ecommerce_product_export" export job page
    And I press the "Edit" button
    And I visit the "Global settings" tab
    Then I should see the text "date format dd/mm/yyyy"
    And I move backward one page
    When I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
      """
      sku;categories;enabled;family;groups;additional_colors;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE-EUR;customs_tax-de_DE-GBP;customs_tax-de_DE-USD;datasheet;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;handmade;image;legend-de_DE;legend-en_GB;legend-en_US;legend-fr_FR;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price-EUR;price-GBP;price-USD;release_date-ecommerce;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
      sweater-white;men_2013,men_2014,men_2015;1;tshirts;;;red;;;;;;;;;;Ein elegantes weißes TShirt;;;Un TShirt blanche élégante;;;;;;;american_apparel;leather;Weißes TShirt;;;TShirt blanche;;10.90;9.00;15.00;28/10/1999;size_M;;;;;
      sweater-yellow;men_2013,men_2014,men_2015;1;tshirts;;;red;;;;;;;;;;Ein elegantes gelb TShirt;;;Un TShirt jaune élégant;;;;;;;american_apparel;leather;Gelb TShirt;;;TShirt jaune;;10.90;9.00;15.00;;size_M;;;;;
    """
