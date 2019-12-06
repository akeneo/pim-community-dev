@javascript
Feature: Export products according to simple select values
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to their simple selected option

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku    | family | name-en_US | manufacturer | categories      |
      | BOOT-1 | boots  | The boot 1 | Nike         | 2014_collection |
      | BOOT-2 | boots  | The boot 2 | Converse     | 2014_collection |
      | BOOT-3 | boots  | The boot 3 |              | 2014_collection |
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/footwear_product_export.csv |

  Scenario: Export only the product values with selected option
    Given I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job edit page
    And I visit the "Content" tab
    And I add available attributes Manufacturer
    And I filter by "manufacturer.code" with operator "In list" and value "Nike,Converse"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    When I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      BOOT-1;;;;1;boots;;;Nike;"The boot 1";;;;;;;
      BOOT-2;;;;1;boots;;;Converse;"The boot 2";;;;;;;
      """
