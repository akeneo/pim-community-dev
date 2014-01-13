@javascript
Feature: Convert metric values during export
  In order to homogeneize exported metric values
  As Julia
  I need to be able to define in which unit to convert metric values during export

  Background:
    Given an "apparel" catalog configuration
    And the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
    And I am logged in as "Julia"

  Scenario: Succesfully display metric conversion configuration for a channel
    Given I am on the "tablet" channel page
    Then I should see "Pick a conversion unit for each metric attribute that will be used during product export" fields:
      | Washing temperature |
      | Weight              |

  Scenario: Succesfully convert metric values
    Given the following channel "ecommerce" conversion options:
      | weight | GRAM |
    And the following products:
      | sku          | family  | categories      | price                 | size   | color | manufacturer     | washing_temperature | weight     | material |
      | tshirt-white | tshirts | 2014_collection | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | 60 CELSIUS          | 5 KILOGRAM | cotton   |
    And the following product values:
      | product      | attribute   | value                        | locale | scope     |
      | tshirt-white | name        | White t-shirt                | en_US  |           |
      | tshirt-white | name        | White t-shirt                | en_GB  |           |
      | tshirt-white | name        | T-shirt blanc                | fr_FR  |           |
      | tshirt-white | name        | Weißes T-Shirt               | de_DE  |           |
      | tshirt-white | description | A stylish white t-shirt      | en_US  | ecommerce |
      | tshirt-white | description | An elegant white t-shirt     | en_GB  | ecommerce |
      | tshirt-white | description | Un T-shirt blanc élégant     | fr_FR  | ecommerce |
      | tshirt-white | description | Ein elegantes weißes T-Shirt | de_DE  | ecommerce |
    And I launched the completeness calculator
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;family;groups;categories;additional_colors;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE-EUR;customs_tax-de_DE-GBP;customs_tax-de_DE-USD;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;handmade;image;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price-EUR;price-GBP;price-USD;release_date-ecommerce;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit;enabled;legend-de_DE;legend-en_GB;legend-en_US;legend-fr_FR
    tshirt-white;tshirts;;2014_collection;;white;;;;;;;;;"Ein elegantes weißes T-Shirt";"An elegant white t-shirt";"A stylish white t-shirt";"Un T-shirt blanc élégant";;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";"White t-shirt";"T-shirt blanc";;10.00;9.00;15.00;;size_M;;60.0000;CELSIUS;5000.0000;GRAM;1;;;;
    """
