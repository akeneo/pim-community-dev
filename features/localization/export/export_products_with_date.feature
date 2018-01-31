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
      | sku           | family  | categories                   |
      | sandal-white  | sandals | men_2013, men_2014, men_2015 |
      | sandal-yellow | sandals | men_2013, men_2014, men_2015 |
    And the following product values:
      | product       | attribute    | value                        | locale | scope     |
      | sandal-white  | name         | Sandale blanche              | fr_FR  |           |
      | sandal-white  | name         | Weißes Sandal                | de_DE  |           |
      | sandal-white  | price        | 10.90 EUR,15 USD,9 GBP       |        |           |
      | sandal-white  | description  | Une Sandale blanche élégante | fr_FR  | ecommerce |
      | sandal-white  | description  | Ein elegantes weißes Sandal  | de_DE  | ecommerce |
      | sandal-white  | release_date | 1999-10-28                   |        | ecommerce |
      | sandal-yellow | name         | Sandale jaune                | fr_FR  |           |
      | sandal-yellow | name         | Gelb Sandal                  | de_DE  |           |
      | sandal-yellow | price        | 10.90 EUR,15 USD,9 GBP       |        |           |
      | sandal-yellow | description  | Une Sandale jaune élégante   | fr_FR  | ecommerce |
      | sandal-yellow | description  | Ein elegantes gelb Sandal    | de_DE  | ecommerce |

  Scenario: Export dates attributes in a specified format
    Given the following job "ecommerce_product_export" configuration:
      | filePath   | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
      | dateFormat | dd/MM/yyyy                                                  |
    And I launched the completeness calculator
    When I am on the "ecommerce_product_export" export job page
    And I press the "Edit" button
    And I visit the "Global settings" tab
    Then I should see the text "date format dd/mm/yyyy"
    And I move backward one page
    When I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
      """
      sku;categories;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;name-de_DE;name-en_GB;name-en_US;name-fr_FR;price-EUR;price-GBP;price-USD;release_date-ecommerce
      sandal-white;men_2013,men_2014,men_2015;"Ein elegantes weißes Sandal";;;"Une Sandale blanche élégante";1;sandals;;"Weißes Sandal";;;"Sandale blanche";10.90;9.00;15.00;28/10/1999
      sandal-yellow;men_2013,men_2014,men_2015;"Ein elegantes gelb Sandal";;;"Une Sandale jaune élégante";1;sandals;;"Gelb Sandal";;;"Sandale jaune";10.90;9.00;15.00;
      """
