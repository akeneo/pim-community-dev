@javascript
Feature: Export products with localized numbers
  In order to use the enriched product data
  As a product manager
  I need to be able to export the localized products to several channels

  Background:
    Given an "apparel" catalog configuration
    And the following attributes:
      | code   | label             | type   | decimals_allowed | negative_allowed | default_metric_unit | metric_family | group |
      | cotton | Percentage cotton | number | true             | false            |                     |               | other |
      | metric | New metric        | metric | true             | true             | GRAM                | Weight        | other |
    And the following family:
      | code   | attributes     |
      | sandal | cotton, metric |
    And the following products:
      | sku           | family  | categories                   | price                    |
      | sandal-white  | sandals | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP |
      | sandal-black  | sandals | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP |
      | sandal-yellow | sandals | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP |
      | sandal-blue   | sandals | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP |
    And the following product values:
      | product       | attribute   | value                          | locale | scope     |
      | sandal-white  | name        | Sandale blanche                | fr_FR  |           |
      | sandal-white  | name        | Weißes Sandal                  | de_DE  |           |
      | sandal-white  | description | Une Sandale blanche élégante   | fr_FR  | ecommerce |
      | sandal-white  | description | Ein elegantes weißes Sandal    | de_DE  | ecommerce |
      | sandal-black  | name        | Sandale noire                  | fr_FR  |           |
      | sandal-black  | name        | Schwarzes Sandal               | de_DE  |           |
      | sandal-black  | description | Une Sandale noire élégante     | fr_FR  | ecommerce |
      | sandal-black  | description | Ein elegantes schwarzes Sandal | de_DE  | ecommerce |
      | sandal-yellow | name        | Sandale jaune                  | fr_FR  |           |
      | sandal-yellow | name        | Gelb Sandal                    | de_DE  |           |
      | sandal-yellow | description | Une Sandale jaune élégante     | fr_FR  | ecommerce |
      | sandal-yellow | description | Ein elegantes gelb Sandal      | de_DE  | ecommerce |
      | sandal-blue   | name        | Sandale bleue                  | fr_FR  |           |
      | sandal-blue   | name        | Blau Sandal                    | de_DE  |           |
      | sandal-blue   | description | Un Sandale bleue élégante      | fr_FR  | ecommerce |
      | sandal-blue   | description | Ein elegantes blau Sandal      | de_DE  | ecommerce |
    And I am logged in as "Julia"

  Scenario: Export number attributes with the correct decimals formatting
    Given the following job "ecommerce_product_export" configuration:
      | filePath         | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
      | decimalSeparator | ,                                                           |
    And the following product values:
      | product       | attribute   | value |
      | sandal-white  | cotton      | 75.55 |
      | sandal-black  | cotton      | 75    |
      | sandal-yellow | cotton      |       |
      | sandal-blue   | cotton      | 75.00 |
    And I launched the completeness calculator
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
      """
      sku;categories;cotton;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;name-de_DE;name-en_GB;name-en_US;name-fr_FR;price-EUR;price-GBP;price-USD
      sandal-white;men_2013,men_2014,men_2015;75,5500;"Ein elegantes weißes Sandal";;;"Une Sandale blanche élégante";1;sandals;;"Weißes Sandal";;;"Sandale blanche";10,90;9,00;15,00
      sandal-black;men_2013,men_2014,men_2015;75,0000;"Ein elegantes schwarzes Sandal";;;"Une Sandale noire élégante";1;sandals;;"Schwarzes Sandal";;;"Sandale noire";10,90;9,00;15,00
      sandal-yellow;men_2013,men_2014,men_2015;;"Ein elegantes gelb Sandal";;;"Une Sandale jaune élégante";1;sandals;;"Gelb Sandal";;;"Sandale jaune";10,90;9,00;15,00
      sandal-blue;men_2013,men_2014,men_2015;75,0000;"Ein elegantes blau Sandal";;;"Un Sandale bleue élégante";1;sandals;;"Blau Sandal";;;"Sandale bleue";10,90;9,00;15,00
      """

  Scenario: Export metric attributes with the correct decimals formatting
    Given the following job "ecommerce_product_export" configuration:
      | filePath         | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
      | decimalSeparator | ,                                                           |
    And the following product values:
      | product       | attribute | value        |
      | sandal-white  | metric    | 90.0000 GRAM |
      | sandal-black  | metric    | 95.5500 GRAM |
      | sandal-yellow | metric    | 85 GRAM      |
      | sandal-blue   | metric    | -5 GRAM      |
    And I launched the completeness calculator
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
      """
      sku;categories;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;metric;metric-unit;name-de_DE;name-en_GB;name-en_US;name-fr_FR;price-EUR;price-GBP;price-USD
      sandal-white;men_2013,men_2014,men_2015;"Ein elegantes weißes Sandal";;;"Une Sandale blanche élégante";1;sandals;;90,0000;GRAM;"Weißes Sandal";;;"Sandale blanche";10,90;9,00;15,00
      sandal-black;men_2013,men_2014,men_2015;"Ein elegantes schwarzes Sandal";;;"Une Sandale noire élégante";1;sandals;;95,5500;GRAM;"Schwarzes Sandal";;;"Sandale noire";10,90;9,00;15,00
      sandal-yellow;men_2013,men_2014,men_2015;"Ein elegantes gelb Sandal";;;"Une Sandale jaune élégante";1;sandals;;85,0000;GRAM;"Gelb Sandal";;;"Sandale jaune";10,90;9,00;15,00
      sandal-blue;men_2013,men_2014,men_2015;"Ein elegantes blau Sandal";;;"Un Sandale bleue élégante";1;sandals;;-5,0000;GRAM;"Blau Sandal";;;"Sandale bleue";10,90;9,00;15,00
      """
