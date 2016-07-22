@javascript
Feature: Export products according to multi select reference data values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their reference data values

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku    | family | name-en_US | manufacturer |
      | BOOT-1 | boots  | The boot 1 | Nike         |
      | BOOT-2 | boots  | The boot 2 | Converse     |
      | BOOT-3 | boots  | The boot 3 |              |
    And the following jobs:
      | connector            | type   | alias              | code               | label              |
      | Akeneo CSV Connector | export | csv_product_export | csv_product_export | CSV product export |
    And the following job "csv_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |

  Scenario: Export only the product values with selected reference data value
    Given I am logged in as "Julia"
    And I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Manufacturer
    And I filter by "manufacturer.code" with operator "In list" and value "Nike"
    And I press the "Save" button
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      BOOT-1;;;;1;boots;;;Nike;"The boot 1";;;;;;;
      """

  Scenario: Export only the product values with selected reference data values
    Given I am logged in as "Julia"
    And I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Manufacturer
    And I filter by "manufacturer.code" with operator "In list" and value "Nike,Converse"
    And I press the "Save" button
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      BOOT-1;;;;1;boots;;;Nike;"The boot 1";;;;;;;
      BOOT-2;;;;1;boots;;;Converse;"The boot 2";;;;;;;
      """

  Scenario: Export only the product values without reference data values
    Given I am logged in as "Julia"
    And I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Manufacturer
    And I filter by "manufacturer.code" with operator "Is empty" and value ""
    And I press the "Save" button
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      BOOT-3;;;;1;boots;;;;"The boot 3";;;;;;;
      """

  Scenario: Export all the product values when no reference data is provided with operator IN LIST
    Given I am logged in as "Julia"
    And I am on the "csv_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Manufacturer
    And I filter by "manufacturer.code" with operator "In list" and value ""
    And I press the "Save" button
    When I launch the export job
    And I wait for the "csv_product_export" job to finish
    Then exported file of "csv_product_export" should contain:
     """
     sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
     BOOT-1;;;;1;boots;;;Nike;"The boot 1";;;;;;;
     BOOT-2;;;;1;boots;;;Converse;"The boot 2";;;;;;;
     BOOT-3;;;;1;boots;;;;"The boot 3";;;;;;;
     """
