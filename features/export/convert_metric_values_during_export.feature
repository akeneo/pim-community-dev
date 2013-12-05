Feature: Convert metric values during export
  In order to homogeneize exported metric values
  As Julia
  I need to be able to define in which unit to convert metric values during export

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"

  Scenario: Succesfully display metric conversion configuration for a channel
    Given I am on the "tablet" channel page
    Then I should see "Pick a conversion unit for each metric attributes that will be used during product export" fields:
      | Washing temperature |
      | Weight              |

  @javascript
  Scenario: Succesfully convert metric values
    Given I have configured channel "ecommerce" with the following conversion options:
      | weight | GRAM |
    And the following products:
      | sku          | family  | categories      |
      | tshirt-white | tshirts | 2014_collection |
    And the following product values:
      | product      | attribute           | value                        | locale | scope     |
      | tshirt-white | Name                | White t-shirt                | en_US  |           |
      | tshirt-white | Name                | White t-shirt                | en_GB  |           |
      | tshirt-white | Name                | T-shirt blanc                | fr_FR  |           |
      | tshirt-white | Name                | Weißes T-Shirt               | de_DE  |           |
      | tshirt-white | Description         | A stylish white t-shirt      | en_US  | ecommerce |
      | tshirt-white | Description         | An elegant white t-shirt     | en_GB  | ecommerce |
      | tshirt-white | Description         | Un T-shirt blanc élégant     | fr_FR  | ecommerce |
      | tshirt-white | Description         | Ein elegantes weißes T-Shirt | de_DE  | ecommerce |
      | tshirt-white | Price               | 10 EUR, 15 USD, 9 GBP        |        |           |
      | tshirt-white | Size                | size_M                       |        |           |
      | tshirt-white | Color               | white                        |        |           |
      | tshirt-white | Manufacturer        | american_apparel             |        |           |
      | tshirt-white | material            | cotton                       |        |           |
      | tshirt-white | Weight              | 5 KILOGRAM                   |        |           |
      | tshirt-white | Washing temperature | 60 CELSIUS                   |        |           |
    And I launched the completeness calculator
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;family;groups;categories;additional_colors;color;cost;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;handmade;image;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price;release_date-ecommerce;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit;enabled
    tshirt-white;tshirts;;2014_collection;;white;;;;;"Ein elegantes weißes T-Shirt";"An elegant white t-shirt";"A stylish white t-shirt";"Un T-shirt blanc élégant";;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";"White t-shirt";"T-shirt blanc";;"10.00 EUR,9.00 GBP,15.00 USD";;size_M;;60.0000;CELSIUS;5000;GRAM;1
    """
