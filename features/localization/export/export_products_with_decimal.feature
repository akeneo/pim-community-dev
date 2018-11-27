@javascript
Feature: Export products with localized numbers
  In order to use the enriched product data
  As a product manager
  I need to be able to export the localized products to several channels

  Background:
    Given an "apparel" catalog configuration
    And the following attributes:
      | code   | label-en_US       | type               | decimals_allowed | negative_allowed | default_metric_unit | metric_family | group |
      | cotton | Percentage cotton | pim_catalog_number | 1                | 0                |                     |               | other |
      | metric | New metric        | pim_catalog_metric | 1                | 1                | GRAM                | Weight        | other |
    And the following family:
      | code   | attributes    |
      | sandal | cotton,metric |
    And the following products:
      | sku           | family  | categories                   | price                    |
      | sandal-white  | sandal | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP |
      | sandal-black  | sandal | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP |
      | sandal-yellow | sandal | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP |
      | sandal-blue   | sandal | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP |
    And I am logged in as "Julia"

  Scenario: Export number attributes with the correct decimals formatting
    Given the following job "ecommerce_product_export" configuration:
      | filePath         | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
      | decimalSeparator | ,                                                           |
    And the following product values:
      | product       | attribute | value |
      | sandal-white  | cotton    | 75.55 |
      | sandal-black  | cotton    | 75    |
      | sandal-yellow | cotton    |       |
      | sandal-blue   | cotton    | 75.00 |
    And I launched the completeness calculator
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
      """
      sku;categories;enabled;family;groups;cotton;metric;metric-unit
      sandal-white;men_2013,men_2014,men_2015;1;sandal;;75,5500;;
      sandal-black;men_2013,men_2014,men_2015;1;sandal;;75,0000;;
      sandal-yellow;men_2013,men_2014,men_2015;1;sandal;;;;
      sandal-blue;men_2013,men_2014,men_2015;1;sandal;;75,0000;;
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
      sku;categories;enabled;family;groups;cotton;metric;metric-unit
      sandal-white;men_2013,men_2014,men_2015;1;sandal;;;90,0000;GRAM
      sandal-black;men_2013,men_2014,men_2015;1;sandal;;;95,5500;GRAM
      sandal-yellow;men_2013,men_2014,men_2015;1;sandal;;;85,0000;GRAM
      sandal-blue;men_2013,men_2014,men_2015;1;sandal;;;-5,0000;GRAM
      """
