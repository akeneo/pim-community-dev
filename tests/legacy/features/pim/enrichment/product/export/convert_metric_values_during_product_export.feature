@javascript
Feature: Export of metric values
  In order to homogeneize exported metric values
  As a product manager
  I need to be able to define in which unit to convert metric values during export

  Background:
    Given an "apparel" catalog configuration
    And the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
    And I am logged in as "Julia"

  Scenario: Successfully convert metric values
    Given the following channel "ecommerce" conversion options:
      | weight | GRAM |
    And the following products:
      | sku          | family  | categories | price                 | size   | color | manufacturer     | washing_temperature | weight     | material |
      | tshirt-white | tshirts | men_2014   | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | 60 CELSIUS          | 5 KILOGRAM | cotton   |
      | tshirt-black | tshirts | men_2014   | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | 0 CELSIUS           | 5 KILOGRAM | cotton   |
    And the following product values:
      | product      | attribute   | value                         | locale | scope     |
      | tshirt-white | name        | White t-shirt                 | en_US  |           |
      | tshirt-white | name        | White t-shirt                 | en_GB  |           |
      | tshirt-white | name        | T-shirt blanc                 | fr_FR  |           |
      | tshirt-white | name        | Weißes T-Shirt                | de_DE  |           |
      | tshirt-white | description | A stylish white t-shirt       | en_US  | ecommerce |
      | tshirt-white | description | An elegant white t-shirt      | en_GB  | ecommerce |
      | tshirt-white | description | Un T-shirt blanc élégant      | fr_FR  | ecommerce |
      | tshirt-white | description | Ein elegantes weißes T-Shirt  | de_DE  | ecommerce |
      | tshirt-black | name        | Black t-shirt                 | en_US  |           |
      | tshirt-black | name        | Black t-shirt                 | en_GB  |           |
      | tshirt-black | name        | T-shirt Black                 | fr_FR  |           |
      | tshirt-black | name        | Schwarz T-Shirt               | de_DE  |           |
      | tshirt-black | description | A stylish black t-shirt       | en_US  | ecommerce |
      | tshirt-black | description | An elegant black t-shirt      | en_GB  | ecommerce |
      | tshirt-black | description | Un T-shirt noir élégant       | fr_FR  | ecommerce |
      | tshirt-black | description | Ein elegantes schwarz T-Shirt | de_DE  | ecommerce |
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE-EUR;customs_tax-de_DE-GBP;customs_tax-de_DE-USD;datasheet;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;handmade;image;legend-de_DE;legend-en_GB;legend-en_US;legend-fr_FR;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price-EUR;price-GBP;price-USD;release_date-ecommerce;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    tshirt-white;;men_2014;white;;;;;;;;;;"Ein elegantes weißes T-Shirt";"An elegant white t-shirt";"A stylish white t-shirt";"Un T-shirt blanc élégant";1;tshirts;;;;;;;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";"White t-shirt";"T-shirt blanc";;10.00;9.00;15.00;;size_M;;60;CELSIUS;5000;GRAM
    tshirt-black;;men_2014;white;;;;;;;;;;"Ein elegantes schwarz T-Shirt";"An elegant black t-shirt";"A stylish black t-shirt";"Un T-shirt noir élégant";1;tshirts;;;;;;;;american_apparel;cotton;"Schwarz T-Shirt";"Black t-shirt";"Black t-shirt";"T-shirt Black";;10.00;9.00;15.00;;size_M;;;;5000;GRAM
    """
